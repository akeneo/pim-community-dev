<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RedirectToEditCatalogActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;
    private CommandBus $catalogCommandBus;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
        $this->catalogCommandBus = $this->get(CommandBus::class);
    }

    public function test_it_is_redirected_to_the_catalog_edit_page():void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
        );

        $userIdentifier = $this->getConnectionUserIdentifier('connected_app');

        $catalogId = '0607c0f8-4ef2-4177-9242-5d864f5d5379';
        $this->catalogCommandBus->execute(new CreateCatalogCommand(
            $catalogId,
            'Test Catalog',
            $userIdentifier,
        ));

        $this->client->request(
            'GET',
            '/connect/apps/v1/catalogs/'. $catalogId,
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        \assert($response instanceof RedirectResponse);
        Assert::assertEquals('/#/connect/connected-apps/connected_app/catalogs/0607c0f8-4ef2-4177-9242-5d864f5d5379', $response->getTargetUrl());
    }

    private function getConnectionUserIdentifier(string $connectionCode): string
    {
        $selectQuery = <<<SQL
        SELECT oro_user.username
        FROM akeneo_connectivity_connection connection
        JOIN oro_user ON oro_user.id = connection.user_id
        WHERE connection.code = :connection_code
        SQL;

        return $this->connection->executeQuery($selectQuery, ['connection_code' => $connectionCode])->fetchOne();
    }
}
