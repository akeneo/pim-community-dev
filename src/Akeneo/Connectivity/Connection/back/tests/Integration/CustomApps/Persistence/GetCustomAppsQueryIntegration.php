<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppsQuery
 */
class GetCustomAppsQueryIntegration extends TestCase
{
    private ?CustomAppLoader $customAppLoader;
    private ?GetCustomAppsQuery $getCustomAppsQuery;
    private ?UserLoader $userLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppLoader = $this->get(CustomAppLoader::class);
        $this->getCustomAppsQuery = $this->get(GetCustomAppsQuery::class);
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

        $this->customAppLoader->create('d2173d05-7748-4fc6-8467-55d1eb84872b', $userB->getId());

        $results = $this->getCustomAppsQuery->execute($userA->getId());

        $this->assertEmpty($results);
    }

    public function test_it_returns_only_custom_apps_for_the_requested_user(): void
    {
        $userA = $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $userB = $this->userLoader->createUser('userB', ['userGroupB'], ['ROLE_APP_B']);
        $userId = $userA->getId();

        $this->customAppLoader->create(
            clientId: '3d5286d9-49b6-403f-aada-f891e18debc8',
            userId: $userId,
            name: 'My test app',
            activateUrl: 'http://shopware.example.com/activate',
            callbackUrl: 'http://shopware.example.com/callback',
        );

        $this->customAppLoader->create(
            clientId: 'd2173d05-7748-4fc6-8467-55d1eb84872b',
            userId: $userB->getId(),
            name: 'My test app 2',
            activateUrl: 'http://shopware.example2.com/activate',
            callbackUrl: 'http://shopware.example2.com/callback',
        );

        $this->customAppLoader->create(
            clientId: '897fa702-7321-4417-9ba5-ea908a4612bf',
            userId: $userId,
            name: 'My test app 3',
            activateUrl: 'http://shopware.example3.com/activate',
            callbackUrl: 'http://shopware.example3.com/callback',
        );

        $results = $this->getCustomAppsQuery->execute($userId);

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
}
