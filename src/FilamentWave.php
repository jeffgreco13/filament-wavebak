<?php

namespace Jeffgreco13\FilamentWave;

use MaxGraphQL\Types\Query;
use Illuminate\Support\Facades\Http;

class FilamentWave
{
    const AUTHURL = "https://api.waveapps.com/oauth2/authorize";
    const TOKENURL = "https://api.waveapps.com/oauth2/token/";
    const REVOKEURL = "https://api.waveapps.com/oauth2/token-revoke";
    const QUERYURL = "https://gql.waveapps.com/graphql/public";

    protected ?string $accessToken;
    protected ?string $businessId;
    protected ?string $clientId;
    protected ?string $clientSecret;

    protected $queryObject;
    protected array $queryArguments = [
        'page' => 1,
        'pageSize' => 10,
    ];
    protected ?string $key;

    public function __construct(?string $accessToken = null, ?string $businessId = null, ?string $clientId = null, ?string $clientSecret = null)
    {
        $this->accessToken = $accessToken ?? env('WAVE_ACCESS_TOKEN');
        $this->businessId = $businessId ?? env('WAVE_BUSINESS_ID');
        $this->clientId = $clientId ?? env('WAVE_CLIENT_ID');
        $this->clientSecret = $clientSecret ?? env('WAVE_CLIENT_SECRET');
    }

    protected function execute(string $query)
    {
        $query = ["query" => $query];
        $request = Http::withToken($this->accessToken)->asJson()->post(
            self::QUERYURL,
            $query
        );
        if ($request->failed()) {
            throw new \Exception("Wave returned a failure. Check your request and try again.");
        }
        return $request->json();
    }

    protected function validate(array $properties = [])
    {
        foreach ($properties as $property) {
            if (empty($this->{$property})) {
                throw new \Exception("Property {$property} cannot be empty");
            }
        }
    }

    public function accessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function rawQuery(string $query)
    {
        return $this->execute($query);
    }

    public function getBusinesses(
        array $fields = ['id', 'name'],
        array $arguments = [
            'page' => 1,
            'pageSize' => 10
        ]
    ) {
        $this->validate(['accessToken']);
        $this->key = 'businesses';
        $this->queryObject = new Query($this->key);
        $this->queryObject->addSelect([
            'pageInfo' => [
                'currentPage',
                'totalPages',
                'totalCount'
            ],
            'edges' => [
                'node' => $fields
            ]
        ]);
        $this->queryObject->addArguments($arguments);

        $responseData = $this->execute($this->queryObject->getPreparedQuery());

        $pageInfo = data_get($responseData, "data.{$this->key}.pageInfo", null);
        $edgeData = collect(data_get($responseData, "data.{$this->key}.edges", []))->map(function ($item) {
            return $item['node'];
        });

        return array_merge([
            "pageInfo" => $pageInfo,
            'records' => $edgeData
        ]);
    }

    public function getCustomers(
        array $fields = ['id', 'name', 'email'],
        array $arguments = [
            'page' => 1,
            'pageSize' => 10
        ]
    ) {
        $this->validate(['accessToken']);

        $this->key = 'customers';
        $this->fields = $fields;
        $this->queryObject = new Query('business');
        $this->queryObject->addArguments(['id' => $this->businessId]);
        $args = collect($arguments)->implode(function ($value, $key) {
            return "{$key}: {$value}";
        }, ', ');
        $this->queryObject->addSelect([
            "{$this->key}({$args})" => [
                'pageInfo' => [
                    'currentPage',
                    'totalPages',
                    'totalCount'
                ],
                'edges' => [
                    'node' => $fields
                ]
            ]
        ]);
        $responseData = $this->execute($this->queryObject->getPreparedQuery());

        $pageInfo = data_get($responseData, "data.business.{$this->key}.pageInfo", null);
        $edgeData = collect(data_get($responseData, "data.business.{$this->key}.edges", []))->map(function ($item) {
            return $item['node'];
        });

        return array_merge([
            "pageInfo" => $pageInfo,
            'records' => $edgeData
        ]);
    }


}
