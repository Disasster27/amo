<?php

declare(strict_types=1);

namespace src\services;

use League\OAuth2\Client\Token\AccessToken;

class TokenService
{
    private $tokenInfo = 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json';

    /**
     * @param array $accessToken
     */
    public function saveToken(array $accessToken)
    {
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];

            file_put_contents($this->tokenInfo, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * @return AccessToken
     */
    public function getSavedToken()
    {
        if (!file_exists($this->tokenInfo)) {
            return false;
        }

        $accessToken = json_decode(file_get_contents($this->tokenInfo), true);

        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            return false;
        }
    }
}