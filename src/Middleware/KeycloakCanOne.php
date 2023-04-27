<?php

namespace Kogeva\LaravelOauth2Adapter\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class KeycloakCanOne extends Oauth2Authenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (empty($guards) && Auth::check()) {
            return $next($request);
        }

        $guards = explode('|', ($guards[0] ?? ''));
        foreach ($guards as $guard) {
            if (Auth::hasRole($guard)) {
                return $next($request);
            }
        }

        throw new AuthorizationException('Forbidden', 403);
    }
}