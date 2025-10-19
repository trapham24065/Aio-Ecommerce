<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ApiResource\Error;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Validation\ValidationException;

final class ValidationErrorProvider implements ProviderInterface
{

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $context['request'];
        if (!$request || !($exception = $request->attributes->get('exception'))) {
            throw new \RuntimeException();
        }

        $status = $operation->getStatus() ?? 422;

        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $violations = [];
            $detailMessages = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $violations[] = [
                        'propertyPath' => $field,
                        'message'      => $message,
                    ];
                    $detailMessages[] = "{$field}: {$message}";
                }
            }

            $response = [
                'title'      => 'An error occurred',
                'detail'     => 'Validation errors: '.implode('; ', $detailMessages),
                'violations' => $violations,
                'status'     => $status,
            ];

            return $response;
        }

        $error = Error::createFromException($exception, $status);

        if ($status >= 500) {
            $error->setDetail('Something went wrong');
        }

        return $error;
    }

}


