<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\GetTestAppsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\GetTestAppsQuery
 */
class GetTestAppsQueryIntegration extends TestCase
{
    private GetTestAppsQuery $query;
    private Connection $connection;
    private UserLoader $userLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetTestAppsQuery::class);
        $this->connection = $this->get('database_connection');
        $this->userLoader = $this->get(UserLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_does_not_return_any_app(): void
    {
        $userA = $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $userB = $this->userLoader->createUser('userB', ['userGroupB'], ['ROLE_APP_B']);

        $this->createTestApp([
            'client_id' => 'd2173d05-7748-4fc6-8467-55d1eb84872b',
            'client_secret' => 'barfoo2',
            'name' => 'My test app 2',
            'activate_url' => 'http://shopware.example2.com/activate',
            'callback_url' => 'http://shopware.example2.com/callback',
            'user_id' => $userB->getId(),
        ]);

        $results = $this->query->execute($userA->getId());

        $this->assertEmpty($results);
    }

    public function test_it_returns_only_test_apps_for_the_requested_user(): void
    {
        $userA = $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $userB = $this->userLoader->createUser('userB', ['userGroupB'], ['ROLE_APP_B']);
        $userId = $userA->getId();

        $this->createTestApp([
            'client_id' => '3d5286d9-49b6-403f-aada-f891e18debc8',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $userId,
        ]);

        $this->createTestApp([
            'client_id' => 'd2173d05-7748-4fc6-8467-55d1eb84872b',
            'client_secret' => 'barfoo2',
            'name' => 'My test app 2',
            'activate_url' => 'http://shopware.example2.com/activate',
            'callback_url' => 'http://shopware.example2.com/callback',
            'user_id' => $userB->getId(),
        ]);

        $this->createTestApp([
            'client_id' => '897fa702-7321-4417-9ba5-ea908a4612bf',
            'client_secret' => 'barfoo3',
            'name' => 'My test app 3',
            'activate_url' => 'http://shopware.example3.com/activate',
            'callback_url' => 'http://shopware.example3.com/callback',
            'user_id' => $userId,
        ]);

        $results = $this->query->execute($userId);

        $this->assertEquals(
            [
                [
                    'client_id' => '3d5286d9-49b6-403f-aada-f891e18debc8',
                    'name' => 'My test app',
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                ],
                [
                    'client_id' => '897fa702-7321-4417-9ba5-ea908a4612bf',
                    'name' => 'My test app 3',
                    'activate_url' => 'http://shopware.example3.com/activate',
                    'callback_url' => 'http://shopware.example3.com/callback',
                ]
            ],
            $results
        );
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
