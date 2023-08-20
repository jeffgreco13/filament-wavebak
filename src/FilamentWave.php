<?php

namespace Jeffgreco13\FilamentWave;

use Log;
use MaxGraphQL\Types\Mutation;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesProducts;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesCustomers;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesBusinesses;
use Jeffgreco13\FilamentWave\REST\Actions\ManagesCurrencies;

class FilamentWave
{
    use ManagesCustomers, ManagesBusinesses, ManagesProducts, ManagesCurrencies;

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

    public function execute(string $query,array $variables = [])
    {
        $query = ['query' => $query];
        if (!empty($variables)){
            $query['variables'] = $variables;
        }
        $response = Http::withToken($this->accessToken)->asJson()->post(
            self::QUERYURL,
            $query
        );
        if ($response->failed()) {
            $errors = collect(data_get($response->json(), 'errors', []));
            $error = $errors->first();
            $code = data_get($error, 'extensions.code', null);
            $message = data_get($error, 'message', null);
            Log::debug($error);
            switch ($code) {
                case 'GRAPHQL_VALIDATION_FAILED':
                    throw new Exceptions\MalformedQueryException("Malformed GraphQL query: {$message}");
                    break;
                case 'NOT_FOUND':
                    throw new Exceptions\ResourceNotFoundException("Resource not found: {$message}");
                    break;
                case 'UNAUTHENTICATED':
                    throw new Exceptions\AuthenticationException("Authentication failed: {$message}");
                    break;
                case 'INTERNAL_SERVER_ERROR':
                    throw new Exceptions\ExecutionException("Execution error: {$message}");
                    break;
                default:
                    throw new \Exception('Wave GraphQL request failed with an unknown error.');
            }

        }

        return $response->json();
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

    protected function buildMutationString(string $name, string $inputType, array $selectFields = [])
    {
        $query = new Mutation($name.' (input: $input)');
        $query->addSelect([
            'didSucceed',
            'inputErrors' => [
                'code',
                'message',
                'path'
            ],

        ]);
        $query->addSelect($selectFields);
        // Hack the mutation string (required)
        $queryStr = str($query->getPreparedQuery())->replace('mutation', 'mutation ($input: '.$inputType.')');
        return $queryStr;
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

    public function businessId(string $businessId)
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
