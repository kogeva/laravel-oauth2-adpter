<?php

namespace Kogeva\LaravelOauth2Adapter\Exceptions;

use RuntimeException;

class Oauth2CallbackException extends RuntimeException
{
    public function __construct(string $error = '')
    {
        $message = '[Oauth2 Error] ' . $error;

        parent::__construct($message);
    }
}