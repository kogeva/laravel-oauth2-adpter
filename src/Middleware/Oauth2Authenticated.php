<?php

namespace Kogeva\LaravelOauth2Adapter\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class Oauth2Authenticated extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        return route('oauth2.login');
    }
}