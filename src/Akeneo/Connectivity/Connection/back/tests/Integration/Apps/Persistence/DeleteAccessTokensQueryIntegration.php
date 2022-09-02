<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DeleteAccessTokensQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAccessTokensQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private DeleteAccessTokensQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(DeleteAccessTokensQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_deletes_any_existing_access_token(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'app_id',
            'foo',
            ['read_products', 'write_products']
        );

        $deleteCount = $this->query->execute('app_id');

        Assert::assertEquals(1, $deleteCount, 'One row should be deleted');
    }

    public function test_it_does_nothing_on_unknown_app_id(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'app_id',
            'foo',
            ['read_products', 'write_products']
        );

        $deleteCount = $this->query->execute('random_id');

        Assert::assertEquals(0, $deleteCount, 'No rows should be deleted');
    }
}
