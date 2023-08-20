<?php

namespace Jeffgreco13\FilamentWave\Commands;

use Illuminate\Console\Command;
use Jeffgreco13\FilamentWave\Facades\FilamentWave;

class FetchWaveCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wave:fetch-currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will fetch compliant currencies from Wave and save them to a json file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FilamentWave::fetchCurrencies();
    }
}
