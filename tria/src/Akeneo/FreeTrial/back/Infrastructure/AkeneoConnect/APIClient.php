<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Infrastructure\RetrievePimFQDN;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class APIClient
{
    public const URI_CONNECT = '/auth/realms/connect/protocol/openid-connect/token';
    public const URI_INVITE_USER = '/api/v2/console/trial/invite';

    private string $clientId;

    private string $clientSecret;

    private string $userName;

    private string $password;

    private ClientInterface $connectApi;

    private ClientInterface $portalApi;

    private RetrievePimFQDN $retrievePimFQDN;

    public function __construct(
        ClientInterface $connectApi,
        ClientInterface $portalApi,
        RetrievePimFQDN $retrievePimFQDN,
        string $clientId,
        string $clientSecret,
        string $userName,
        string $password
    ) {
        $this->connectApi = $connectApi;
        $this->portalApi = $portalApi;
        $this->retrievePimFQDN = $retrievePimFQDN;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userName = $userName;
        $this->password = $password;
    }

    public function inviteUser(string $email): ResponseInterface
    {
        $token = $this->connect();

        return $this->portalApi->request('POST', self::URI_INVITE_USER, [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $token),
            ],
            'json' => [
                'fqdn' => ($this->retrievePimFQDN)(),
                'email' => $email,
            ],
            'http_errors' => false,
        ]);
    }

    private function connect(): string
    {
        $params = [
            'username' => $this->userName,
            'password' => $this->password,
            'grant_type' => 'password',
            'client_secret' => $this->clientSecret,
            'client_id' => $this->clientId,
        ];

        $response = $this->connectApi->request('POST', self::URI_CONNECT, [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => $params,
        ]);

        $response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($response['access_token'])) {
            throw new \Exception('Invalid authentication response from Akeneo Connect API');
        }

        return $response['access_token'];
    }
}
