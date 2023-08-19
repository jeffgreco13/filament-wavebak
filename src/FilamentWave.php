<?php

namespace Jeffgreco13\FilamentWave;

use Illuminate\Support\Facades\Http;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesBusinesses;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesCustomers;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesProducts;
use Laravel\Socialite\Facades\Socialite;

class FilamentWave
{
    use ManagesCustomers, ManagesBusinesses, ManagesProducts;

    const AUTHURL = 'https://api.waveapps.com/oauth2/authorize';

    const TOKENURL = 'https://api.waveapps.com/oauth2/token/';

    const REVOKEURL = 'https://api.waveapps.com/oauth2/token-revoke';

    const QUERYURL = 'https://gql.waveapps.com/graphql/public';

    protected ?string $accessToken;

    protected ?string $businessId;

    protected ?string $clientId;

    protected ?string $clientSecret;

    protected int $page = 1;

    protected int $pageSize = 10;

    protected ?string $cachedMethod;

    public function __construct(string $accessToken = null, string $businessId = null, string $clientId = null, string $clientSecret = null)
    {
        $this->accessToken = $accessToken ?? env('WAVE_ACCESS_TOKEN');
        $this->businessId = $businessId ?? env('WAVE_BUSINESS_ID');
        $this->clientId = $clientId ?? env('WAVE_CLIENT_ID');
        $this->clientSecret = $clientSecret ?? env('WAVE_CLIENT_SECRET');
    }

    public function execute(string $query)
    {
        $query = ['query' => $query];
        $request = Http::withToken($this->accessToken)->asJson()->post(
            self::QUERYURL,
            $query
        );
        if ($request->failed()) {
            throw new \Exception('Wave returned a failure. Check your request and try again.');
        }

        return $request->json();
    }

    protected function validate(array $properties = [])
    {
        foreach ($properties as $property) {
            if (empty($this->{$property})) {
                throw new \Exception("Property {$property} cannot be empty.");
            }
        }
    }

    public function cachedMethod(string $cachedMethod)
    {
        $this->cachedMethod = $cachedMethod;

        return $this;
    }

    public function getCachedMethod()
    {
        return $this->cachedMethod;
    }

    protected function prepareArguments(array $arguments): string
    {
        return collect([
            'page' => $this->getPage(),
            'pageSize' => $this->getPageSize(),
        ])
            ->merge($arguments)
            ->implode(function ($value, $key) {
                return "{$key}: {$value}";
            }, ', ');
    }

    public function page(int $page)
    {
        $this->page = $page;

        return $this;
    }

    public function nextPage()
    {
        $this->page++;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function pageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function accessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function businessId(int $businessId)
    {
        $this->businessId = $businessId;

        return $this;
    }

    public function getBusinessId(): string
    {
        return $this->businessId;
    }

    public function rawQuery(string $query)
    {
        return $this->execute($query);
    }

    public function socialite()
    {
        return Socialite::driver('wave');
    }
}
