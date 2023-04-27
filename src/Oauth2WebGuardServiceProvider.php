<?php

namespace Kogeva\LaravelOauth2Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Kogeva\LaravelOauth2Adapter\Auth\Guard\Oauth2WebGuard;
use Kogeva\LaravelOauth2Adapter\Auth\Oauth2WebUserProvider;
use Kogeva\LaravelOauth2Adapter\Middleware\KeycloakCanOne;
use Kogeva\LaravelOauth2Adapter\Middleware\Oauth2Authenticated;
use Kogeva\LaravelOauth2Adapter\Middleware\Oauth2Can;
use Kogeva\LaravelOauth2Adapter\Services\Oauth2Service;

class Oauth2WebGuardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Configuration
        $config = __DIR__ . '/../config/oauth2-web.php';

        $this->publishes([$config => config_path('oauth2-web.php')], 'config');
        $this->mergeConfigFrom($config, 'oauth2-web');

        // User Provider
        Auth::provider('oauth2-users', function ($app, array $config) {
            return new Oauth2WebUserProvider($config['model']);
        });

        // Gate
        Gate::define('oauth2-web', function ($user, $roles, $resource = '') {
            return $user->hasRole($roles, $resource) ?: null;
        });
    }

    public function register()
    {
        // Keycloak Web Guard
        Auth::extend('oauth2-web', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new Oauth2WebGuard($provider, $app->request);
        });

        // Facades
        $this->app->bind('oauth2-web', function ($app) {
            return $app->make(Oauth2Service::class);
        });

        // Routes
        $this->registerRoutes();

        // Middleware Group
        $this->app['router']->middlewareGroup('oauth2-web', [
            StartSession::class,
            Oauth2Authenticated::class,
        ]);

        // Add Middleware "keycloak-web-can"
        $this->app['router']->aliasMiddleware('oauth2-web-can', Oauth2Can::class);

        // Add Middleware "keycloak-web-can-one
        $this->app['router']->aliasMiddleware('oauth2-web-can-one', KeycloakCanOne::class);

        // Bind for client data
        $this->app->when(Oauth2Service::class)->needs(ClientInterface::class)->give(function () {
            return new Client(Config::get('oauth2-web.guzzle_options', []));
        });
    }

    protected function registerRoutes()
    {
        $defaults = [
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
            'callback' => 'callback',
        ];

        $routes = Config::get('oauth2-web.routes', []);
        $routes = array_merge($defaults, $routes);

        // Register Routes
        $router = $this->app->make('router');

        if (!empty($routes['login'])) {
            $router->middleware('web')->get(
                $routes['login'],
                'Kogeva\LaravelOauth2Adapter\Controllers\AuthController@login'
            )->name('oauth2.login');
        }

        if (!empty($routes['logout'])) {
            $router->middleware('web')->get(
                $routes['logout'],
                'Kogeva\LaravelOauth2Adapter\Controllers\AuthController@logout'
            )->name('oauth2.logout');
        }

        if (!empty($routes['register'])) {
            $router->middleware('web')->get(
                $routes['register'],
                'Kogeva\LaravelOauth2Adapter\Controllers\AuthController@register'
            )->name('oauth2.register');
        }

        if (!empty($routes['callback'])) {
            $router->middleware('web')->get(
                $routes['callback'],
                'Kogeva\LaravelOauth2Adapter\Controllers\AuthController@callback'
            )->name('oauth2.callback');
        }
    }
}