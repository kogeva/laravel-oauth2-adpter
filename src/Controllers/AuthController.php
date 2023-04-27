<?php

namespace Kogeva\LaravelOauth2Adapter\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kogeva\LaravelOauth2Adapter\Exceptions\Oauth2CallbackException;
use Kogeva\LaravelOauth2Adapter\Facades\Oauth2Web;

class AuthController
{
    public function login()
    {
        Oauth2Web::saveState();

        return redirect(Oauth2Web::getLoginUrl());
    }

    public function logout()
    {
        Oauth2Web::forgetToken();

        return redirect(Oauth2Web::getLogoutUrl());
    }

    public function register()
    {
        return redirect(Oauth2Web::getRegisterUrl());
    }

    public function callback(Request $request)
    {
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new Oauth2CallbackException($error);
        }

        $state = $request->input('state');
        if (empty($state) || ! Oauth2Web::validateState($state)) {
            Oauth2Web::forgetState();

            throw new Oauth2CallbackException('Invalid state');
        }

        $code = $request->input('code');
        if (! empty($code)) {
            $token = Oauth2Web::getAccessToken($code);

            if (Auth::validate($token)) {
                return redirect()->intended(config('oauth2.redirect_url', '/'));
            }
        }

        return redirect(route('oauth2.login'));
    }
}