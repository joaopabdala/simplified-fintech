<?php

namespace App\Services\Authorization;

interface AuthorizationInterface {
    public function isAuthorized(): bool;
}
