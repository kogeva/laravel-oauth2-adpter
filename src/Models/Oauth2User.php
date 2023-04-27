<?php

namespace Kogeva\LaravelOauth2Adapter\Models;

use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @property mixed|null $id
 */
class Oauth2User implements Authenticatable
{
    protected array $fillable = [
        'id',
        'username',
        'email'
    ];

    protected array $attributes = [];

    public function __construct(array $profile)
    {
        foreach ($profile as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[ $key ] = $value;
            }
        }

        $this->id = $this->getKey();
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getKey()
    {
        return $this->id;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        $this->id;
    }

    public function getAuthPassword()
    {
        throw new BadMethodCallException('Unexpected method [getAuthPassword] call');
    }

    public function getRememberToken()
    {
        throw new BadMethodCallException('Unexpected method [getRememberToken] call');
    }

    public function setRememberToken($value)
    {
        throw new BadMethodCallException('Unexpected method [setRememberToken] call');
    }

    public function getRememberTokenName()
    {
        throw new BadMethodCallException('Unexpected method [getRememberTokenName] call');
    }
}