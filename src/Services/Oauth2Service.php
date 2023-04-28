<?php

namespace Kogeva\LaravelOauth2Adapter\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Oauth2Service
{
    public static string $sessionKey = '_oauth_token';

    public static string $sessionStateKey = '_oauth_state';

    protected string $baseUrl;

    protected bool $cacheOpenid;

    protected string $callbackUrl;

    protected string $redirectLogout;

    protected string $clientId;

    protected string $clientSecret;

    protected string $state;

    protected Client $httpClient;

    /**
     * @throws Exception
     */
    public function __construct(ClientInterface $client)
    {
        $this->baseUrl = trim(Config::get('oauth2-web.base_url'), '/');
        $this->clientId = Config::get('oauth2-web.client_id');
        $this->clientSecret = Config::get('oauth2-web.client_secret');
        $this->cacheOpenid = Config::get('oauth2-web.cache_openid', false);
        $this->callbackUrl = route('oauth2.callback');
        $this->redirectLogout = Config::get('oauth2-web.redirect_logout', '');

        $this->httpClient = $client;

        $this->state = bin2hex(random_bytes(16));
    }

    public function getLoginUrl(array $params = []): string
    {
        $baseParams = [
            'scope' => $params['scope'] ?? '',
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->callbackUrl,
            'state' => $this->getState(),
        ];

        $params = array_merge($baseParams, $params);

        return $this->buildUrl(Config::get('oauth2-web.authorization_endpoint'), $params);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLogoutUrl(): string
    {
        if (empty($this->redirectLogout)) {
            $this->redirectLogout = url('/');
        }

        $params = [
            'client_id' => $this->getClientId()
        ];
        $token = $this->retrieveToken();
        if (!empty($token['id_token'])) {
            $params['post_logout_redirect_uri'] = $this->redirectLogout;
            $params['id_token_hint'] = $token['id_token'];
        }

        return $this->buildUrl(Config::get('oauth2-web.end_session_endpoint'), $params);
    }

    public function getAccessToken($code)
    {
        $url = $this->baseUrl . Config::get('oauth2-web.token_endpoint');

        $params = [
            'code' => $code,
            'client_id' => $this->getClientId(),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callbackUrl,
            'client_secret' => $this->clientSecret
        ];

        return $this->tokenRequest($url, $params);
    }

    public function refreshAccessToken(array $credentials)
    {
        if (empty($credentials['refresh_token'])) {
            return [];
        }

        $url = $this->baseUrl . Config::get('oauth2-web.token_endpoint');

        $params = [
            'client_id' => $this->getClientId(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $credentials['refresh_token'],
            'redirect_uri' => $this->callbackUrl,
            'client_secret' => $this->clientSecret
        ];

        return $this->tokenRequest($url, $params);
    }

    public function invalidateRefreshToken($refreshToken): bool
    {
        $url = $this->baseUrl . Config::get('oauth2-web.end_session_endpoint');

        $params = [
            'client_id' => $this->getClientId(),
            'refresh_token' => $refreshToken,
        ];

        if (! empty($this->clientSecret)) {
            $params['client_secret'] = $this->clientSecret;
        }

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params]);
            return $response->getStatusCode() === 204;
        } catch (GuzzleException $e) {
            $this->logException($e);
        }

        return false;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function retrieveToken(): ?array
    {
        return session()->get(self::$sessionKey);
    }

    public function saveToken(array $credentials): void
    {
        session()->put(Oauth2Service::$sessionKey, $credentials);
        session()->save();
    }

    public function forgetToken()
    {
        session()->forget(Oauth2Service::$sessionKey);
        session()->save();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validateState(string $state): bool
    {
        return (session()->get(self::$sessionStateKey, null) === $state);
    }

    public function saveState(): void
    {
        session()->put(self::$sessionStateKey, $this->state);
        session()->save();
    }

    public function forgetState(): void
    {
        session()->forget(self::$sessionStateKey);
        session()->save();
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function buildUrl(string $urlPath, array $params): string
    {
        return $this->baseUrl . $urlPath . '?' . http_build_query($params);
    }

    protected function logException(GuzzleException $e)
    {
        // Guzzle 7
        if (! method_exists($e, 'getResponse') || empty($e->getResponse())) {
            Log::error('[Keycloak Service] ' . $e->getMessage());
            return;
        }

        $error = [
            'request' => method_exists($e, 'getRequest') ? $e->getRequest() : '',
            'response' => $e->getResponse()->getBody()->getContents(),
        ];

        Log::error('[Keycloak Service] ' . print_r($error, true));
    }

    /**
     * @param string $url
     * @param array $params
     * @return mixed|string|null
     */
    public function tokenRequest(string $url, array $params):? array
    {
        $token = null;

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params]);

            if ($response->getStatusCode() === 200) {
                $token = $response->getBody()->getContents();
                $token = json_decode($token, true);
            }
        } catch (GuzzleException $e) {
            $this->logException($e);
        }

        return $token;
    }
}