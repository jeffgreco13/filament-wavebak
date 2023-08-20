<?php

namespace Jeffgreco13\FilamentWave;

use Illuminate\Support\Facades\Event;
use Jeffgreco13\FilamentWave\Commands\FetchWaveCurrencies;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWaveServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-wave')
            ->hasCommands([FetchWaveCurrencies::class]);
        // ->hasMigration('create_filament_wave_customers_table');
    }

    public function registeringPackage()
    {
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Wave\WaveExtendSocialite::class.'@handle'
        );
    }

    public function bootingPackage()
    {
        $migrationFileName = 'create_wave_customers_table';
        $filePath = $this->package->basePath("/../database/migrations/{$migrationFileName}.php");
        $this->publishes([
            $filePath => $this->generateMigrationName(
                $migrationFileName,
                now()->addSecond()
            ),
        ], "{$this->package->shortName()}-customers-migration");
    }
}
