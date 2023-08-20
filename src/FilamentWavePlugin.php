<?php

namespace Jeffgreco13\FilamentWave;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Jeffgreco13\FilamentWave\Models\Customer;
use Jeffgreco13\FilamentWave\Resources\CustomerResource;

class FilamentWavePlugin implements Plugin
{
    protected bool $hasCustomers = false;

    protected $customerResource;

    protected $customerModel;

    public function getId(): string
    {
        return 'wave';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $resources = [];
        if ($this->hasCustomers()) {
            $resources[] = $this->getCustomerResource();
        }
        $panel
            ->resources($resources)
            ->pages([
                //
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function customers(bool $condition = true, $resource = CustomerResource::class, $model = Customer::class): static
    {
        $this->hasCustomers = $condition;
        $this->customerResource = $resource;
        $this->customerModel = $model;

        return $this;
    }

    public function hasCustomers(): bool
    {
        return $this->hasCustomers;
    }

    public function getCustomerResource()
    {
        return $this->customerResource;
    }

    public function getCustomerModel()
    {
        return $this->customerModel;
    }
}
