<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class APIClient
{
    private const INVITE_USER_URI = '/api/v1/console/trial/invite';

    private string $clientId;

    private string $clientSecret;

    private string $userName;

    private string $password;

    private string $akeneoConnectBaseUri;

    private string $token;

    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        string $clientId,
        string $clientSecret,
        string $userName,
        string $password,
        string $akeneoConnectBaseUri
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userName = $userName;
        $this->password = $password;
        $this->httpClient = $httpClient;
        $this->akeneoConnectBaseUri = $akeneoConnectBaseUri;
    }

    public function inviteUser(string $email): void
    {
        $this->httpClient->request('POST');
    }

}
