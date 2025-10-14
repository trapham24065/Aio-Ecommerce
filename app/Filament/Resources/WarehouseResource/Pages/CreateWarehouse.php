<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{

    protected static string $resource = WarehouseResource::class;

    protected function getFormOptions(): array
    {
        return [
            'attributes' => [
                'novalidate' => true,
            ],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }

}
