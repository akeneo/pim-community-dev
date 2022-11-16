<?php

namespace AkeneoEnterprise\Connectivity\Connection\Tests\Integration\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllAppsUserGroupLabelQuery;

class GetAllAppsUserGroupLabelQueryIntegration extends TestCase
{
    private GetAllAppsUserGroupLabelQuery $query;
    private ConnectedAppLoader $connectedAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAllAppsUserGroupLabelQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_returns_an_array_of_connected_app_labels_indexed_by_connected_app_codes(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2777e764-f852-4956-bf9b-1a1ec1b0d146',
            'bar'
        );

        $result = $this->query->execute();

        $this->assertEquals([
            [
                'code' => 'app_bar',
                'label' => 'bar',
            ],
            [
                'code' => 'app_foo',
                'label' => 'foo',
            ],
        ], $result);
    }
}
