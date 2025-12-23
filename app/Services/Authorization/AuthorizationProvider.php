<?php

namespace App\Services\Authorization;

use App\Services\Authorization\Adapters\MockedAuthorizationAdapter;
use App\Services\Authorization\Adapters\UtilDeviAuthorizationAdapter;

class AuthorizationProvider
{
    public static function make()
    {
        $provider = config('services.authorization.provider');

        return match ($provider) {
            'util-devi-tools' => app(UtilDeviAuthorizationAdapter::class),
            'local' => app(MockedAuthorizationAdapter::class),
            default => throw new \Exception("Provider '{$provider}' not supported")
        };
    }
}
