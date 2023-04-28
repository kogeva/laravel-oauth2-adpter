<?php

namespace Kogeva\LaravelOauth2Adapter\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Kogeva\LaravelOauth2Adapter\Exceptions\Oauth2CallbackException;
use Kogeva\LaravelOauth2Adapter\Facades\Oauth2Web;

class Oauth2WebGuard implements Guard
{
    protected ?Authenticatable $user = null;
    private UserProvider $provider;
    private Request $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function check(): bool
    {
        return (bool) $this->user();
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if (empty($this->user)) {
            $this->authenticate();
        }

        return $this->user;
    }

    public function id()
    {
        return $this->user()->id ?? null;
    }

    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        Oauth2Web::saveToken($credentials);

        return $this->authenticate();
    }

    public function setUser(?Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function authenticate(): bool
    {
        $credentials = Oauth2Web::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = Oauth2Web::getUserProfile($credentials);
        if (empty($user)) {
            Oauth2Web::forgetToken();

            if (Config::get('app.debug', false)) {
                throw new Oauth2CallbackException('User cannot be authenticated.');
            }

            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);

        return true;
    }

    public function roles()
    {
        //TODO RELEASE
    }

    public function hasRole($roles, $resource = '')
    {
        //TODO RELEASE
    }
}