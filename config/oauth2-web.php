<?php

return [
    /**
     * Keycloak Url
     *
     * Generally https://your-server.com/auth
     */
    'base_url' => env('OAUTH2_BASE_URL', ''),

    /**
     * The Keycloak Server realm public key (string).
     *
     * @see Keycloak >> Realm Settings >> Keys >> RS256 >> Public Key
     */
    'public_key' => env('OAUTH2_PUBLIC_KEY', null),

    'authorization_endpoint' => '/oauth/authorize',

    'token_endpoint' => '/oauth/token',

    'end_session_endpoint' => '',

    /**
     * Client ID
     */
    'client_id' => env('OAUTH2_CLIENT_ID', null),

    /**
     * Client Secret
     */
    'client_secret' => env('OAUTH2_CLIENT_SECRET', null),

    'cache_openid' => env('CACHE_OPENID', false),

    /**
     * Page to redirect after callback if there's no "intent"
     */
    'redirect_url' => '/admin',

    /**
     * The routes for authenticate
     */
    'routes' => [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'callback' => 'callback',
    ],

    /**
     * GuzzleHttp Client options
     *
     * @link http://docs.guzzlephp.org/en/stable/request-options.html
     */
    'guzzle_options' => [],
];