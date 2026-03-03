<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAppDeletionQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppDeletionQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private GetAppDeletionQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get(GetAppDeletionQuery::class);
    }

    public function test_it_gets_an_app_to_delete_from_the_database(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento'
        );

        $result = $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        $expected = new AppDeletion(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
            'app_magento',
            'ROLE_MAGENTO',
        );

        $this->assertEquals($expected, $result);
    }
}
