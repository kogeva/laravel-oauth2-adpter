<?php

namespace Kogeva\LaravelOauth2Adapter\Auth;

use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class Oauth2WebUserProvider implements UserProvider
{
    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        throw new BadMethodCallException('Unexpected method [retrieveById] call');
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new BadMethodCallException('Unexpected method [retrieveByToken] call');
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new BadMethodCallException('Unexpected method [updateRememberToken] call');
    }

    public function retrieveByCredentials(array $credentials)
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class($credentials);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new BadMethodCallException('Unexpected method [validateCredentials] call');
    }
}
