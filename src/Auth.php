<?php

declare(strict_types=1);

namespace src;

use AmoCRM\Client\AmoCRMApiClient;
use src\services\TokenService;
use League\OAuth2\Client\Token\AccessTokenInterface;
use src\services\CrmHandlerService;

class Auth
{
    private $apiClient;
    private $tokenService;

    public function __construct()
    {
        $this->apiClient = new AmoCRMApiClient(string $clientId, string $clientSecret, ?string $redirectUri);
        $this->tokenService = new TokenService();
    }

    public function run()
    {
        if ($accessToken = $this->tokenService->getSavedToken()) {
            $this->setSavedToken($accessToken);
        } elseif (!isset($_GET['code']) && $accessToken) {
            header('Location: ' . $this->apiClient->getOAuthClient()->getAuthorizeUrl(['mode' => 'popup',]));
            die;
        } elseif (isset($_GET['code']) && $_GET['code']) {
            $this->getNewToken();
        }

        $crmHandler = new CrmHandlerService();
        $contacts = $crmHandler->getContacts($this->apiClient);
        $crmHandler->addTasks($contacts, $this->apiClient);
    }

    public function setSavedToken($accessToken)
    {
        $baseDomain = $accessToken->getValues()['baseDomain'];

        $this->apiClient->setAccessToken($accessToken)
            ->setAccountBaseDomain($baseDomain)
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    $this->tokenService->saveToken(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $baseDomain,
                        ]
                    );
                }
            );
    }

    public function getNewToken()
    {
        try {
            $accessToken = $this->apiClient->setAccountBaseDomain($_GET['referer'])->getOAuthClient()->getAccessTokenByCode($_GET['code']);
            $this->apiClient->setAccessToken($accessToken);
            if (!$accessToken->hasExpired()) {
                $this->tokenService->saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $this->apiClient->getAccountBaseDomain(),
                ]);
            }
        } catch (\Exception $e) {
            die((string)$e);
        }
    }
}
