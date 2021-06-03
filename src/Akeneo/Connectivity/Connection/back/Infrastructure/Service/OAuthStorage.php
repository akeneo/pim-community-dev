<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use FOS\OAuthServerBundle\Storage\GrantExtensionDispatcherInterface;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage as BaseOAuthStorage;
use OAuth2\IOAuth2GrantClient;
use OAuth2\IOAuth2GrantCode;
use OAuth2\IOAuth2GrantExtension;
use OAuth2\IOAuth2GrantImplicit;
use OAuth2\IOAuth2GrantUser;
use OAuth2\IOAuth2RefreshTokens;
use OAuth2\Model\IOAuth2Client;

class OAuthStorage implements IOAuth2RefreshTokens, IOAuth2GrantUser, IOAuth2GrantCode, IOAuth2GrantImplicit,
    IOAuth2GrantClient, IOAuth2GrantExtension, GrantExtensionDispatcherInterface
{
    private BaseOAuthStorage $baseOAuthStorage;

    public function __construct(BaseOAuthStorage $baseOAuthStorage)
    {
        $this->baseOAuthStorage = $baseOAuthStorage;
    }

    public function checkClientCredentials(IOAuth2Client $client, $clientSecret = null)
    {
        return true;
    }

    public function setGrantExtension($uri, GrantExtensionInterface $grantExtension)
    {
        $this->baseOAuthStorage->setGrantExtension($uri, $grantExtension);
    }

    public function checkClientCredentialsGrant(IOAuth2Client $client, $clientSecret)
    {
        return $this->baseOAuthStorage->checkClientCredentialsGrant($client, $clientSecret);
    }

    public function getAuthCode($code)
    {
        return $this->baseOAuthStorage->getAuthCode($code);
    }

    public function createAuthCode($code, IOAuth2Client $client, $data, $redirectUri, $expires, $scope = null)
    {
        return $this->baseOAuthStorage->createAuthCode($code, $client, $data, $redirectUri, $expires, $scope);
    }

    public function markAuthCodeAsUsed($code)
    {
        $this->baseOAuthStorage->markAuthCodeAsUsed($code);
    }

    public function checkGrantExtension(IOAuth2Client $client, $uri, array $inputData, array $authHeaders)
    {
        return $this->baseOAuthStorage->checkGrantExtension($client, $uri, $inputData, $authHeaders);
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        return $this->baseOAuthStorage->checkUserCredentials($client, $username, $password);
    }

    public function getRefreshToken($refreshToken)
    {
        return $this->baseOAuthStorage->getRefreshToken($refreshToken);
    }

    public function createRefreshToken($refreshToken, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        return $this->baseOAuthStorage->createRefreshToken($refreshToken, $client, $data, $expires, $scope);
    }

    public function unsetRefreshToken($refreshToken)
    {
        $this->baseOAuthStorage->unsetRefreshToken($refreshToken);
    }

    public function getClient($clientId)
    {
        return $this->baseOAuthStorage->getClient($clientId);
    }

    public function getAccessToken($oauthToken)
    {
        return $this->baseOAuthStorage->getAccessToken($oauthToken);
    }

    public function createAccessToken($oauthToken, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        return $this->baseOAuthStorage->createAccessToken($oauthToken, $client, $data, $expires, $scope);
    }

    public function checkRestrictedGrantType(IOAuth2Client $client, $grantType)
    {
        return $this->baseOAuthStorage->checkRestrictedGrantType($client, $grantType);
    }
}
