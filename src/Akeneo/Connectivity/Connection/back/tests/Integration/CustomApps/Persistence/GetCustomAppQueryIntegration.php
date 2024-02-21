<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery
 */
class GetCustomAppQueryIntegration extends TestCase
{
    private ?CustomAppLoader $customAppLoader;
    private ?ConnectedAppLoader $connectedAppLoader;
    private ?GetCustomAppQuery $getCustomAppQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppLoader = $this->get(CustomAppLoader::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->getCustomAppQuery = $this->get(GetCustomAppQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_a_custom_app(): void
    {
        $user = $this->createAdminUser();
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        $result = $this->getCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertEquals(
            [
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'name' => 'custom_app_name',
                'author' => 'John Doe',
                'activate_url' => 'http://activate.test',
                'callback_url' => 'http://callback.test',
                'connected' => false,
            ],
            $result
        );
    }

    public function test_it_returns_a_custom_app_which_is_connected(): void
    {
        $user = $this->createAdminUser();
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('100eedac-ff5c-497b-899d-e2d64b6c59f9', 'foo');

        $result = $this->getCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertEquals(
            [
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'name' => 'custom_app_name',
                'author' => 'John Doe',
                'activate_url' => 'http://activate.test',
                'callback_url' => 'http://callback.test',
                'connected' => true,
            ],
            $result
        );
    }

    public function test_it_returns_null_when_custom_app_does_not_exist(): void
    {
        $result = $this->getCustomAppQuery->execute('wrong_id');

        $this->assertNull($result);
    }
}
