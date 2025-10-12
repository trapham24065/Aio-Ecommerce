<?php

namespace App\Filament\Resources\WarehouseResource\Widgets;

use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WarehouseIncomeChart extends ChartWidget
{

    protected int|string|array $columnSpan = ['md' => 12];

    protected static ?string $heading = 'Incoming Stock';

    public ?Warehouse $record = null;

    public ?string $filter = 'week';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week'  => 'This Week',
            'month' => 'This Month',
            'year'  => 'This Year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $query = InventoryTransaction::query()
            ->where('warehouse_id', $this->record->id)
            ->where('type', 'IN');

        // Áp dụng khoảng thời gian dựa trên filter
        [$startDate, $endDate] = match ($activeFilter) {
            'today' => [Carbon::today(), Carbon::tomorrow()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
        };

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Nhóm dữ liệu theo ngày
        $data = $query
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity_change) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('total', 'date')
            ->all();

        $labels = array_keys($data);
        $values = array_values($data);

        return [
            'datasets' => [
                [
                    'label'           => 'Incoming Quantity',
                    'data'            => $values,
                    'backgroundColor' => '#36A2EB',
                    'borderColor'     => '#9BD0F5',
                ],
            ],
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

}
