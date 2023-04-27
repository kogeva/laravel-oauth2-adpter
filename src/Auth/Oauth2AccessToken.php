<?php

namespace Kogeva\LaravelOauth2Adapter\Auth;

class Oauth2AccessToken
{
    protected string $accessToken;
    protected string $refreshToken;
    protected string $idToken;

    protected int $expiredAt;

    public function __construct(array $data = [])
    {
        if (! empty($data['access_token'])) {
            $this->accessToken = $data['access_token'];
        }

        if (! empty($data['refresh_token'])) {
            $this->refreshToken = $data['refresh_token'];
        }

        if (! empty($data['id_token'])) {
            $this->idToken = $data['id_token'];
        }

        if (! empty($data['expires_in'])) {
            $this->expiredAt = (int) $data['expires_in'];
        }
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getIdToken(): string
    {
        return $this->idToken;
    }

    public function getExpiredAt(): int
    {
        return $this->expiredAt;
    }

    public function validateToken()
    {
        //TODO Сделать реализацию
    }

    protected function parseToken(string $token)
    {
        $token = explode('.', $token);
        $token = $this->base64UrlDecode($token[1]);

        return json_decode($token, true);
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}