<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetConnectedAppScopesQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppScopesQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private ConnectedAppLoader $connectedAppLoader;
    private GetConnectedAppScopesQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get(GetConnectedAppScopesQuery::class);
    }

    public function test_it_gets_connected_app_scopes_from_the_database(): void
    {
        $this->connectionLoader->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);
        $this->connectedAppLoader->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1', 'scope A2', 'scope A3'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            ['category A1', 'category A2'],
            false,
            'partner A'
        );

        $result = $this->query->execute('0dfce574-2238-4b13-b8cc-8d257ce7645b');

        $this->assertEquals(['scope A1', 'scope A2', 'scope A3'], $result);
    }

    public function test_it_returns_an_empty_array(): void
    {
        $result = $this->query->execute('undefinedAppId');

        $this->assertEquals([], $result);
    }
}
