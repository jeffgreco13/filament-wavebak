<?php

namespace Jeffgreco13\FilamentWave\REST\Actions;

use MaxGraphQL\Types\Query;
use Illuminate\Support\Collection;
use Jeffgreco13\FilamentWave\REST\Cursor;
use Jeffgreco13\FilamentWave\REST\Customer;

trait ManagesProducts
{
    public function allProducts(...$args): Collection
    {
        $pages = $this->page(1)->pageSize(200)->paginateProducts(...$args);
        $records = collect();
        foreach ($pages as $page){
            $records = $records->merge($page);
        }
        return $records;
    }

    public function paginateProducts(...$args) {
        $data = $this->getProducts(...$args);
        return new Cursor($data['records'], $data['pageInfo'], $this);
    }

    public function getProducts(
        array $fields = ['id', 'name', 'description','isSold','isBought'],
        array $arguments = []
    ) {
        $this->validate(['accessToken']);
        $this->cachedMethod(__FUNCTION__);
        $arguments = $this->prepareArguments($arguments);

        $key = 'products';

        $query = new Query('business');
        $query->addArguments(['id' => $this->getBusinessId()])->addSelect([
            "{$key}({$arguments})" => [
                'pageInfo' => [
                    'currentPage',
                    'totalPages',
                    'totalCount',
                ],
                'edges' => [
                    'node' => $fields,
                ],
            ],
        ]);

        $responseData = $this->execute($query->getPreparedQuery());

        $pageInfo = data_get($responseData, "data.business.{$key}.pageInfo", null);

        $records = collect(data_get($responseData, "data.business.{$key}.edges", []))->map(function ($item) {
                return new Customer($item['node']);
            });

        return [
            'pageInfo' => $pageInfo,
            'records' => $records
        ];
    }
}
