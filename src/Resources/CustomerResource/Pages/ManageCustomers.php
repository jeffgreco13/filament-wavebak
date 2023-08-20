<?php

namespace Jeffgreco13\FilamentWave\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Jeffgreco13\FilamentWave\Facades\FilamentWave;
use Jeffgreco13\FilamentWave\Models\Customer;

class ManageCustomers extends ManageRecords
{
    public static function getResource(): string
    {
        return filament('wave')->getCustomerResource();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('lg'),
            Actions\Action::make('sync')
                ->action(function () {

                    Customer::withoutEvents(function () {
                        $ids = [];
                        FilamentWave::allCustomers()->map(function ($customer) use (&$ids) {
                            $ids[] = $customer->id;
                            Customer::updateOrCreate([
                                'id' => $customer->id,
                            ], [
                                'name' => $customer->name,
                                'email' => $customer->email,
                                'first_name' => $customer->firstName,
                                'last_name' => $customer->lastName,
                                'address' => $customer->address,
                                'phone' => $customer->phone,
                                'currency' => data_get($customer->currency, 'code', null),
                                // I don't think we want Wave dictating archive status anymore. There's nothing in the Wave UI to control this, so let's just use this for internal purposes
                                // 'is_archived' => $customer->isArchived
                            ]);
                        });

                        Customer::whereNotIn('id', $ids)->get()->each->archive();

                    });

                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->active()),
            'archived' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->archived()),
        ];
    }
}
