<?php

namespace App\Services\Authorization\Adapters;

use App\Services\Authorization\AuthorizationInterface;

class MockedAuthorizationAdapter implements AuthorizationInterface
{

    public function isAuthorized(): bool
    {
        usleep(rand(100000, 500000));

        if (rand(1, 3) === 1) {
            return false;
        }

        return true;
    }
}
