<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class DeleteAppHandlerIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private ConnectedAppLoader $connectedAppLoader;
    private UserGroupLoader $userGroupLoader;
    private DeleteAppHandler $deleteAppHandler;

    public function test_to_delete_an_app(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento connection', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_7891011ghijkl']);
        $this->connectedAppLoader->createConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'Magento App',
            ['read_catalog_structure', 'read_products'],
            'magento',
            'http://www.magento.test/path/to/logo/b',
            'Magento Corp.',
            'app_7891011ghijkl',
            ['ecommerce'],
            true,
            null
        );

        $this->connectionLoader->createConnection('akeneo_print', 'Akeneo Print connection', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);
        $this->connectedAppLoader->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'Akeneo Print app',
            ['read_catalog_structure', 'read_products'],
            'akeneo_print',
            'http://www.print.test/path/to/logo/a',
            'author',
            'app_123456abcdef',
            ['print'],
            false,
            'partner'
        );

        $this->deleteAppHandler->handle(new DeleteAppCommand('2677e764-f852-4956-bf9b-1a1ec1b0d145'));

        // TODO CXP-912 Call repo to check if app is still here
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->deleteAppHandler = $this->get('Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
