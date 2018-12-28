<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\UserGroup;

use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

class SqlFindUserGroupsForSecurityIdentifierTest extends SqlIntegrationTestCase
{
    /** @var FindUserGroupsForSecurityIdentifierInterface */
    private $findUserGroupsForSecurityIdentifiers;

    public function setUp()
    {
        parent::setUp();

        $this->findUserGroupsForSecurityIdentifiers = $this->get('akeneo.referencentity.infrastructure.persistence.permission.query.find_user_groups_for_security_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_some_user_groups()
    {
        $this->insertUserGroups();

        $userGroupIdentifiers = ($this->findUserGroupsForSecurityIdentifiers)(SecurityIdentifier::fromString('admin'));
        $normalizedUserGroupIdentifiers = array_map(
            function (UserGroupIdentifier $userGroupIdentifier) {
                return $userGroupIdentifier->normalize();
            },
            $userGroupIdentifiers
        );

        $this->assertEquals([1, 2], $normalizedUserGroupIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_any_user_group()
    {
        $this->assertEmpty(($this->findUserGroupsForSecurityIdentifiers)(SecurityIdentifier::fromString('julia')));
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function insertUserGroups(): void
    {
        $sqlConnection = $this->get('database_connection');
        $sqlConnection->transactional(function (Connection $connection) {
            $insertUserGroups = <<<SQL
INSERT INTO `oro_access_group` (`id`, `name`)
VALUES (1, 'IT support'), (2, 'Catalog Manager');
SQL;
            $connection->executeUpdate($insertUserGroups);
            $insertUserBelongingToGroup = <<<SQL
INSERT INTO `oro_user_access_group` (`user_id`, `group_id`)
VALUES (1, 1), (1, 2);
SQL;
            $connection->executeUpdate($insertUserBelongingToGroup);
        });
    }
}
