<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateUserConsentQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppAuthenticationScopesActionEndToEnd extends WebTestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private CreateUserConsentQuery $createUserConsentQuery;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->createUserConsentQuery = $this->get(CreateUserConsentQuery::class);
    }

    public function test_it_gets_connected_app_consented_authentication_scopes(): void
    {
        $adminUser = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'app_identifier',
            'connection_code'
        );
        $this->createUserConsentQuery->execute(
            $adminUser->getId(),
            'app_identifier',
            ['openid', 'profile', 'email'],
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connection_code/authentication-scopes',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals(['openid', 'profile', 'email'], $result);
    }
}
