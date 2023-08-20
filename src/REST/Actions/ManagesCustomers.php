<?php

namespace Jeffgreco13\FilamentWave\REST\Actions;

use Illuminate\Support\Collection;
use Jeffgreco13\FilamentWave\Exceptions\MalformedQueryException;
use Jeffgreco13\FilamentWave\REST\Cursor;
use Jeffgreco13\FilamentWave\REST\Customer;
use MaxGraphQL\Types\Query;

trait ManagesCustomers
{
    public function allCustomers(...$args): Collection
    {
        $pages = $this->page(1)->pageSize(200)->paginateCustomers(...$args);
        $records = collect();
        foreach ($pages as $page) {
            $records = $records->merge($page);
        }

        return $records;
    }

    public function paginateCustomers(...$args)
    {
        $data = $this->getCustomers(...$args);

        return new Cursor($data['records'], $data['pageInfo'], $this);
    }

    public function getCustomers(
        array $fields = ['id', 'name', 'firstName', 'lastName', 'email', 'phone', 'currency' => ['code'], 'address' => ['addressLine1', 'city', 'province' => ['code', 'name'], 'country' => ['code', 'name'], 'postalCode']],
        array $arguments = ['sort' => 'NAME_ASC']
    ) {
        $this->validate(['accessToken']);
        $this->cachedMethod(__FUNCTION__);
        $arguments = $this->prepareArguments($arguments);

        $key = 'customers';

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
            'records' => $records,
        ];
    }

    public function createCustomer(array $input)
    {
        // When running a mutation, just return the ID of the newly formed object.
        $this->validate(['accessToken', 'businessId']);
        $name = 'customerCreate';
        $input = array_merge($input, [
            'businessId' => $this->getBusinessId(),
        ]);
        $variables = [
            'input' => $input,
        ];
        $queryStr = $this->buildMutationString(
            name: $name,
            inputType: 'CustomerCreateInput!',
            selectFields: [
                'customer' => [
                    'id',
                ],
            ]
        );

        $responseData = $this->execute(query: $queryStr, variables: $variables);
        $success = (bool) data_get($responseData, "data.{$name}.didSucceed", false);
        if (! $success) {
            \Log::debug($responseData);
            throw new MalformedQueryException("{$name} query failed. See log for details.");
        }

        return data_get($responseData, "data.{$name}.customer", null);
    }

    public function updateCustomer(array $input)
    {
        $this->validate(['accessToken']);

        $name = 'customerPatch';
        $variables = [
            'input' => $input,
        ];
        $queryStr = $this->buildMutationString(
            name: $name,
            inputType: 'CustomerPatchInput!',
            selectFields: [
                'customer' => [
                    'id',
                ],
            ]
        );

        $responseData = $this->execute(query: $queryStr, variables: $variables);
        $success = (bool) data_get($responseData, "data.{$name}.didSucceed", false);
        if (! $success) {
            \Log::debug($responseData);
            throw new MalformedQueryException("{$name} query failed. See log for details.");
        }

        return data_get($responseData, "data.{$name}.customer", null);
    }
}
