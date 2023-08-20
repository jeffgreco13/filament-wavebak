<?php

namespace Jeffgreco13\FilamentWave\REST\Actions;

use Illuminate\Support\Collection;
use MaxGraphQL\Types\Query;

trait ManagesCurrencies
{
    public function fetchCurrencies()
    {
        $currencies = $this->allCurrencies();
        file_put_contents(storage_path('wave_currencies.json'), json_encode($currencies));
    }

    public function allCurrencies()
    {
        $query = new Query("currencies");
        $query->addSelect([
            'code',
            'symbol',
            'name',
            'plural',
            'exponent'
        ]);
        $responseData = $this->execute($query->getPrepareDQuery());
        return data_get($responseData,'data.currencies',[]);
    }
}
