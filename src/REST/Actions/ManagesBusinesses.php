<?php

namespace Jeffgreco13\FilamentWave\REST\Actions;

use Illuminate\Support\Collection;
use Jeffgreco13\FilamentWave\REST\Business;
use Jeffgreco13\FilamentWave\REST\Cursor;
use MaxGraphQL\Types\Query;

trait ManagesBusinesses
{
    public function allBusinesses(...$args): Collection
    {
        $pages = $this->page(1)->pageSize(200)->paginateBusinesses(...$args);
        $records = collect();
        foreach ($pages as $page) {
            $records = $records->merge($page);
        }

        return $records;
    }

    public function paginateBusinesses(...$args)
    {
        $data = $this->getBusinesses(...$args);

        return new Cursor($data['records'], $data['pageInfo'], $this);
    }

    public function getBusinesses(
        array $fields = ['id', 'name', 'isPersonal'],
        array $arguments = []
    ) {
        $this->validate(['accessToken']);
        $this->cachedMethod(__FUNCTION__);
        $arguments = $this->prepareArguments($arguments);

        $key = 'businesses';

        $query = new Query("{$key}({$arguments})");
        $query->addSelect([
            'pageInfo' => [
                'currentPage',
                'totalPages',
                'totalCount',
            ],
            'edges' => [
                'node' => $fields,
            ],
        ]);

        $responseData = $this->execute($query->getPreparedQuery());

        $pageInfo = data_get($responseData, "data.{$key}.pageInfo", null);

        $records = collect(data_get($responseData, "data.{$key}.edges", []))->map(function ($item) {
            return new Business($item['node']);
        });

        return [
            'pageInfo' => $pageInfo,
            'records' => $records,
        ];
    }
}
