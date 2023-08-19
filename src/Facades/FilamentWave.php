<?php

namespace Jeffgreco13\FilamentWave\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffgreco13\FilamentWave\FilamentWave
 */
class FilamentWave extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Jeffgreco13\FilamentWave\FilamentWave::class;
    }
}
