<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\GetTestAppQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTestAppQueryIntegration extends TestCase
{
    private GetTestAppQuery $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetTestAppQuery::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_a_test_app()
    {
        $user = $this->createAdminUser();
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $user->getId(),
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertEquals(
            [
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'secret' => 'foobar',
                'name' => 'My test app',
                'author' => 'John Doe',
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
                'connected' => false,
            ],
            $result
        );
    }

    public function test_it_returns_null_when_test_app_does_not_exist()
    {
        $result = $this->query->execute('wrong_id');

        $this->assertNull($result);
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
