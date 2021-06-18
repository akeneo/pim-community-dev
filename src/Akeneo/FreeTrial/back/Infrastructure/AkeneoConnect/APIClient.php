<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Infrastructure\RetrievePimFQDN;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class APIClient
{
    private const INVITE_USER_URI = '/api/v1/console/trial/invite';

    private string $clientId;

    private string $clientSecret;

    private string $userName;

    private string $password;

    private string $akeneoConnectBaseUri;

    private string $token;

    private ClientInterface $httpClient;

    private RetrievePimFQDN $retrievePimFQDN;

    public function __construct(
        ClientInterface $httpClient,
        RetrievePimFQDN $retrievePimFQDN,
        string $clientId,
        string $clientSecret,
        string $userName,
        string $password,
        string $akeneoConnectBaseUri
    ) {
        $this->httpClient = $httpClient;
        $this->retrievePimFQDN = $retrievePimFQDN;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userName = $userName;
        $this->password = $password;
        $this->akeneoConnectBaseUri = $akeneoConnectBaseUri;
    }

    public function inviteUser(string $email): ResponseInterface
    {
        $token = $this->connect();

        return $this->httpClient->request('POST', $this->akeneoConnectBaseUri . '/api/v1/console/trial/invite', [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $token),
            ],
            'body' => [
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

        $response = $this->httpClient->request('POST', $this->akeneoConnectBaseUri . '/auth/realms/connect/protocol/openid-connect/token', [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query($params)
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        if (!isset($response['access_token'])) {
            throw new \Exception('Invalid authentication response from Akeneo Connect API');
        }

        return $response['access_token'];
    }
}
