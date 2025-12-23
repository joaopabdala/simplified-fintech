<?php

namespace App\Services\Authorization;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UtilDeviAuthorizationService
{
    private $httpClient;

    private $endpoint;

    public function __construct()
    {
        $this->httpClient = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->acceptJson();

        $this->endpoint = 'https://util.devi.tools/api/v2/authorize';
    }

    public function authorize(): Response
    {
        return $this->httpClient->retry(3, 100)->get($this->endpoint);
    }
}
