<?php

namespace Kogeva\LaravelOauth2Adapter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static retrieveToken()
 * @method static getUserProfile($credentials)
 * @method static forgetToken()
 * @method static saveToken(array $credentials)
 * @method static getLoginUrl()
 * @method static saveState()
 * @method static getLogoutUrl()
 * @method static getRegisterUrl()
 * @method static getAccessToken(mixed $code)
 * @method static validateState(mixed $state)
 * @method static forgetState()
 */
class Oauth2Web extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'oauth2-web';
    }
}