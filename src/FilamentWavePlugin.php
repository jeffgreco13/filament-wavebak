<?php

namespace Jeffgreco13\FilamentWave;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Jeffgreco13\FilamentWave\Resources\CustomerResource;

class FilamentWavePlugin implements Plugin
{

    protected bool $hasCustomers = false;
    protected $customerResourceClass;

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
        if ($this->hasCustomers()){
            $resources[] = $this->getCustomerResourceClass();
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

    public function customers(bool $condition = true,$resource = CustomerResource::class): static
    {
        $this->hasCustomers = $condition;
        $this->customerResourceClass = $resource;
        return $this;
    }

    public function hasCustomers(): bool
    {
        return $this->hasCustomers;
    }

    public function getCustomerResourceClass()
    {
        return $this->customerResourceClass;
    }
}
