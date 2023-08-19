<?php

namespace Jeffgreco13\FilamentWave\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jeffgreco13\FilamentWave\FilamentWaveServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Jeffgreco13\\FilamentWave\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentWaveServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_filament-wave_table.php.stub';
        $migration->up();
        */
    }
}
