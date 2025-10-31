<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Dto\ProductInput;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\OptionValue;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;

final class ProductProcessor implements ProcessorInterface
{

    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof ProductInput) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            // Auto-generate SKU from name if not provided
            if (!empty($requestData['name']) && empty($requestData['sku'])) {
                $name = $requestData['name'];
                $sanitized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
                $baseSku = \Str::slug($sanitized);
                $baseSku = \Str::limit($baseSku, 100, '');
                $baseSku = trim($baseSku, '-');
                $baseSku = strtoupper($baseSku);

                $finalSku = $baseSku;
                $counter = 1;
                $recordId = $uriVariables['id'] ?? null;

                while (
                Product::where('sku', $finalSku)
                    ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                    ->exists()
                ) {
                    $counter++;
                    $finalSku = $baseSku.'-'.$counter;
                }

                $requestData['sku'] = $finalSku;
            }

            $rules = [
                'type'        => ['required', 'in:simple,variant'],
                'category_id' => ['required', 'exists:categories,id'],
                'supplier_id' => ['nullable', 'exists:suppliers,id'],
                'brand_id'    => ['nullable', 'exists:brands,id'],
                'name'        => ['required', 'string', 'max:100'],
                'sku'         => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:500'],
                'thumbnail'   => ['required', 'string'],
                'quantity'    => ['nullable', 'integer', 'min:0'],
                'flag'        => ['nullable', 'integer'],
                'status'      => ['required', 'boolean'],
            ];

            // Chỉ require base_cost khi type là simple
            if (($requestData['type'] ?? null) === 'simple') {
                $rules['base_cost'] = ['required', 'numeric', 'min:1', 'max:9999999999999.99'];
            } else {
                $rules['base_cost'] = ['nullable', 'numeric', 'min:1', 'max:9999999999999.99'];
            }

            if ($operation->getMethod() === 'POST') {
                $rules['sku'][] = Rule::unique('products');
                $rules['name'][] = Rule::unique('products')->where(function ($query) use ($requestData) {
                    return $query->where('category_id', $requestData['category_id'] ?? null);
                });
            } else {
                $productId = $uriVariables['id'];
                $rules['sku'][] = Rule::unique('products')->ignore($productId);
                $rules['name'][] = Rule::unique('products')->ignore($productId)->where(
                    function ($query) use ($requestData) {
                        return $query->where('category_id', $requestData['category_id'] ?? null);
                    }
                );
            }

            $validator = \Validator::make($requestData, $rules);

            if ($validator->fails()) {
                return ValidationErrorProvider::toJsonResponse($validator->errors());
            }

            $validated = $validator->validated();

            if (isset($validated['status'])) {
                $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN);
            }

            if ($operation->getMethod() === 'POST') {
                $model = new Product($validated);
                $result = $this->persist->process($model, $operation, $uriVariables, $context);

                // Xử lý variants và options sau khi tạo product
                if ($model->type === Product::TYPE_VARIANT && isset($requestData['variants'])) {
                    $this->processVariantsAndOptions($model, $requestData['variants']);
                }

                // Xử lý images nếu có
                if (isset($requestData['images'])) {
                    $this->processImages($model, $requestData['images']);
                }

                return $result;
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Product not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }

    private function processVariantsAndOptions(Product $product, array $variantsData): void
    {
        $optionsMap = [];

        // Tạo options và option values
        foreach ($variantsData as $variantData) {
            if (isset($variantData['options'])) {
                foreach ($variantData['options'] as $optionData) {
                    $optionName = $optionData['name'];
                    $optionValue = $optionData['value'];

                    // Tạo hoặc lấy option
                    if (!isset($optionsMap[$optionName])) {
                        $option = ProductOption::firstOrCreate([
                            'product_id' => $product->id,
                            'name'       => $optionName,
                        ]);
                        $optionsMap[$optionName] = $option;
                    }

                    // Tạo option value - sửa từ option_id thành product_option_id
                    OptionValue::firstOrCreate([
                        'product_option_id' => $optionsMap[$optionName]->id,
                        'value'             => $optionValue,
                    ]);
                }
            }
        }

        // Tạo variants
        foreach ($variantsData as $variantData) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku'        => $variantData['sku'],
                'price'      => $variantData['price'],
                'quantity'   => $variantData['quantity'] ?? 0,
            ]);

            // Liên kết variant với option values
            if (isset($variantData['options'])) {
                $optionValueIds = [];
                foreach ($variantData['options'] as $optionData) {
                    // Sửa relationship query
                    $optionValue = OptionValue::whereHas(
                        'productOption',
                        function ($query) use ($product, $optionData) {
                            $query->where('product_id', $product->id)
                                ->where('name', $optionData['name']);
                        }
                    )->where('value', $optionData['value'])->first();

                    if ($optionValue) {
                        $optionValueIds[] = $optionValue->id;
                    }
                }

                $variant->optionValues()->sync($optionValueIds);
            }
        }
    }

    private function processImages(Product $product, array $imagesData): void
    {
        foreach ($imagesData as $imageData) {
            ProductImage::create([
                'product_id'         => $product->id,
                'product_variant_id' => $imageData['variant_id'] ?? null,
                'url'                => $imageData['url'],
                'alt_text'           => $imageData['alt_text'] ?? null,
                'position'           => $imageData['position'] ?? 0,
            ]);
        }
    }

}




