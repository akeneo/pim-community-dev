<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionClientSecretEndToEnd extends ApiTestCase
{
    public function test_it_disables_the_access_token()
    {
        $connectionWithCredentials = $this->createConnection('magento');

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $connectionWithCredentials->clientId(),
            $connectionWithCredentials->secret(),
            $connectionWithCredentials->username(),
            $connectionWithCredentials->password()
        );
        $apiClient->request('GET', 'api/rest/v1/currencies');
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $regenerateConnectionSecretCommand = new RegenerateConnectionSecretCommand('magento');
        $this->get('akeneo_connectivity.connection.application.handler.regenerate_connection_secret')->handle($regenerateConnectionSecretCommand);

        $apiClient->reload();

        $expectedResponseContent = <<<JSON
{
    "code": 401,
    "message": "The access token provided is invalid."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expectedResponseContent, $apiClient->getResponse()->getContent());
    }

    public function test_it_disables_the_secret()
    {
        $connectionWithCredentials = $this->createConnection('magento');

        $command = new RegenerateConnectionSecretCommand('magento');
        $this->get('akeneo_connectivity.connection.application.handler.regenerate_connection_secret')->handle($command);

        $client = static::createClient(['debug' => false]);
        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => $connectionWithCredentials->username(),
                'password'   => $connectionWithCredentials->password(),
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $connectionWithCredentials->clientId(),
                'PHP_AUTH_PW'   => $connectionWithCredentials->secret(),
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $expectedResponseContent = <<<JSON
{
    "code": 422,
    "message": "Parameter \"client_id\" is missing or does not match any client, or secret is invalid"
}
JSON;
        $this->assertJsonStringEqualsJsonString($expectedResponseContent, $client->getResponse()->getContent());

    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
