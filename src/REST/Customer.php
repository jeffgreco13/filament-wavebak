<?php

namespace Jeffgreco13\FilamentWave\REST;

use Illuminate\Support\Arr;
use Jeffgreco13\FilamentWave\Exceptions\InvalidDataException;
use Jeffgreco13\FilamentWave\Facades\FilamentWave;

class Customer extends Resource
{
    // Create, update, delete methods can go here.
    public function create()
    {
        if ($this->offsetExists('id') && ! is_null($this->getAttribute('id'))) {
            throw new InvalidDataException('Cannot create resource that already has an id.');
        }
        // First, filter out any attributes that might be null.
        $attributes = Arr::whereNotNull($this->getAttributes());

        $customer = FilamentWave::createCustomer($attributes);
        $this->setAttribute('id', $customer['id']);

        return true;
    }

    public function update()
    {
        if (! $this->offsetExists('id') || is_null($this->getAttribute('id'))) {
            throw new InvalidDataException('Cannot update resource with missing or null id.');
        }
        $attributes = Arr::whereNotNull($this->getAttributes());
        $customer = FilamentWave::updateCustomer($attributes);

        return true;
    }
}
