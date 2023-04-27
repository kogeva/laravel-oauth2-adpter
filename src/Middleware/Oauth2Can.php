<?php

namespace Kogeva\LaravelOauth2Adapter\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class Oauth2Can extends Oauth2Authenticated
{
    /**
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (empty($guards) && Auth::check()) {
            return $next($request);
        }

        $guards = explode('|', ($guards[0] ?? ''));
        if (Auth::hasRole($guards)) {
            return $next($request);
        }

        throw new AuthorizationException('Forbidden', 403);
    }
}