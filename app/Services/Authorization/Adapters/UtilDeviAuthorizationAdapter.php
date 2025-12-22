<?php

namespace App\Services\Authorization\Adapters;

use App\Services\Authorization\AuthorizationInterface;
use App\Services\Authorization\UtilDeviAuthorizationService;
use Illuminate\Support\Facades\Log;
use function data_get;

class UtilDeviAuthorizationAdapter implements AuthorizationInterface
{

    protected $service;

    public function __construct(UtilDeviAuthorizationService  $service)
    {
        $this->service = $service;
    }

    public function isAuthorized(): bool
    {
        try {
            $response = $this->service->authorize();
            if ($response->successful()) {
                return (bool) data_get($response->json(), 'data.authorization', false);
            }

            if ($response->status() === 403) {
                return false;
            }

            Log::warning("Authorization service returned an unexpected status: " . $response->status(), [
                'body' => $response->body()
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error("Failed to reach authorization service: " . $e->getMessage());
            return false;
        }

    }
}
