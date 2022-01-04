<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\GetTestAppQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTestAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private GetTestAppQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->query = $this->get(GetTestAppQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_a_test_app_with_an_user()
    {
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => 1,
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');
        $this->assertEquals([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'name' => 'My test app',
            'author' => 'John Doe',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ], $result);
    }

    public function test_it_returns_a_test_app_without_an_user()
    {
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');
        $this->assertEquals([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'name' => 'My test app',
            'author' => null,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ], $result);
    }

    /**
     * @param array{
     *     client_id: string,
     *     client_secret: string,
     *     name: string,
     *     activate_url: string,
     *     callback_url: string,
     *     user_id: string|null,
     * } $data
     */
    private function createTestApp(array $data): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', $data);
    }
}
