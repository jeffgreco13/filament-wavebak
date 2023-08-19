<?php

namespace Jeffgreco13\FilamentWave\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Jeffgreco13\FilamentWave\Facades\FilamentWave;
use Jeffgreco13\FilamentWave\Resources\CustomerResource;

class ManageCustomers extends ManageRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('sync')
                ->action(function(){
                    dd(FilamentWave::getCustomers());
                })
        ];
    }
}
