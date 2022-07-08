<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindAllUsernamesWithAclQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllUsernamesWithAclQueryIntegration extends TestCase
{
    private FindAllUsernamesWithAclQuery $query;
    private UserLoader $userLoader;
    private AclLoader $aclLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindAllUsernamesWithAclQuery::class);
        $this->userLoader = $this->get(UserLoader::class);
        $this->aclLoader = $this->get(AclLoader::class);

        $this->get('feature_flags')->enable('marketplace_activate');
    }

    public function test_it_returns_all_usernames_given_the_acl(): void
    {
        $this->createAdminUser();
        $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $this->userLoader->createUser('userB', ['userGroupB'], ['ROLE_APP_A']);
        $this->userLoader->createUser('userC', ['userGroupC'], ['ROLE_APP_B']);
        $this->userLoader->createUser('userD', ['userGroupC'], ['ROLE_APP_B']);
        $this->userLoader->createUser('userE', ['userGroupC'], ['ROLE_USER']);

        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', [
            'ROLE_APP_A',
            'ROLE_APP_B',
            'ROLE_ADMINISTRATOR'
        ]);

        $foundUsernames = $this->query->execute('akeneo_connectivity_connection_manage_apps');

        $expectedUsernames = [
            'admin',
            'userA',
            'userB',
            'userC',
            'userD',
        ];

        self::assertEqualsCanonicalizing($expectedUsernames, $foundUsernames);
    }

    public function test_it_returns_no_usernames_given_the_acl(): void
    {
        $this->createAdminUser();
        $this->userLoader->createUser('userA', ['userGroupA'], ['ROLE_APP_A']);
        $this->userLoader->createUser('userB', ['userGroupB'], ['ROLE_APP_A']);
        $this->userLoader->createUser('userC', ['userGroupC'], ['ROLE_APP_B']);
        $this->userLoader->createUser('userD', ['userGroupC'], ['ROLE_APP_B']);

        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_apps', [
            'ROLE_USER',
        ]);

        $foundUsernames = $this->query->execute('akeneo_connectivity_connection_manage_apps');

        self::assertEqualsCanonicalizing([], $foundUsernames);
    }
}
