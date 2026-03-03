<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneConnectedAppByIdQueryIntegration extends TestCase
{
    private FindOneConnectedAppByIdQuery $query;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_can_retrieve_an_app(): void
    {
        $connection = $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);

        $createdApp = new ConnectedApp(
            '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
            'my app',
            ['foo', 'bar'],
            'bynder',
            'app logo',
            'app author',
            'app_123456abcdef',
            $connection->username(),
            ['e-commerce'],
            false,
            'akeneo'
        );
        $this->createConnectedAppQuery->execute($createdApp);

        $retrievedApp = $this->query->execute('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        Assert::assertEquals(\serialize($createdApp), \serialize($retrievedApp));
    }

    public function test_it_can_retrieve_a_connected_app_by_id_related_to_a_custom_app(): void
    {
        $connection = $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);

        $createdApp = new ConnectedApp(
            '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
            'my app',
            ['foo', 'bar'],
            'bynder',
            'app logo',
            'app author',
            'app_123456abcdef',
            $connection->username(),
            ['e-commerce'],
            false,
            'akeneo',
            true
        );
        $this->createConnectedAppQuery->execute($createdApp);

        $this->createCustomApp('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        $retrievedApp = $this->query->execute('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        Assert::assertEquals(\serialize($createdApp), \serialize($retrievedApp));
    }

    private function createCustomApp(string $id): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', [
            'client_id' => $id,
            'client_secret' => 'secret',
            'name' => 'App Name',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);
    }
}
