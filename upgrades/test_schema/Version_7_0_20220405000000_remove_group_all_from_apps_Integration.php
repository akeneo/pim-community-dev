<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_7_0_20220405000000_remove_group_all_from_apps_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220405000000_remove_group_all_from_apps';

    private ?Connection $connection;
    private ?ConnectedAppLoader $connectedAppLoader;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testTheGroupAllIsRemovedFromApps(): void
    {
        $this->insertUser('john');
        $this->insertConnection('magento', ConnectionType::DEFAULT_TYPE);
        $this->insertConnection('app_1t1n5nczm36ss00kws0cgckgw', ConnectionType::APP_TYPE);

        $this->assertUserHasGroups('john', ['All', 'Manager']);
        $this->assertUserHasGroups('magento', ['All', 'Manager']);
        $this->assertUserHasGroups('app_1t1n5nczm36ss00kws0cgckgw', ['All', 'Manager']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUserHasGroups('john', ['All', 'Manager']);
        $this->assertUserHasGroups('magento', ['All', 'Manager']);
        $this->assertUserHasGroups('app_1t1n5nczm36ss00kws0cgckgw', ['Manager']);
    }

    private function assertUserHasGroups(string $username, array $expectedGroups): void
    {
        $query = <<<SQL
SELECT oro_access_group.name
FROM oro_access_group
JOIN oro_user_access_group on oro_access_group.id = oro_user_access_group.group_id
JOIN oro_user on oro_user_access_group.user_id = oro_user.id
WHERE oro_user.username = :username
SQL;
        $groups = $this->connection->fetchFirstColumn($query, [
            'username' => $username,
        ]);

        sort($expectedGroups);
        sort($groups);

        Assert::assertSame($expectedGroups, $groups);
    }

    private function insertConnection(string $code, string $type): void
    {
        $oauthClientId = $this->insertOAuthClient();
        $userId = $this->insertUser($code);

        $query = <<<SQL
INSERT INTO akeneo_connectivity_connection (client_id, user_id, code, type, label)
VALUES (:client, :user, :code, :type, '')
SQL;

        $this->connection->executeQuery($query, [
            'client' => $oauthClientId,
            'user' => $userId,
            'code' => $code,
            'type' => $type,
        ]);
    }

    private function insertUser(string $username): int
    {
        $query = <<<SQL
INSERT INTO oro_user (ui_locale_id, username, email, salt, password, createdAt, updatedAt, timezone, properties)
VALUES ((SELECT id FROM pim_catalog_locale WHERE code = 'en_US'), :username, :username, '', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'UTC', '{}')
SQL;

        $this->connection->executeQuery($query, [
            'username' => $username,
        ]);

        $uid = (int) $this->connection->lastInsertId();

        $this->addUserToGroups($uid, ['All', 'Manager']);

        return $uid;
    }

    private function addUserToGroups(int $uid, array $groups): void
    {
        $query = <<<SQL
INSERT INTO oro_user_access_group (user_id, group_id)
VALUES (:user, (SELECT id FROM oro_access_group WHERE name = :group))
SQL;

        foreach ($groups as $group) {
            $this->connection->executeQuery($query, [
                'user' => $uid,
                'group' => $group,
            ]);
        }
    }

    private function insertOAuthClient(): int
    {
        $query = <<<SQL
INSERT INTO pim_api_client (random_id, redirect_uris, secret, allowed_grant_types)
VALUES (:random, 'a:0:{}', :random, 'a:0:{}')
SQL;

        $this->connection->executeQuery($query, [
            'random' => $this->random(),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function random(int $maxLength = 30): string
    {
        return \substr(\base_convert(\bin2hex(\random_bytes(16)), 16, 36), 0, $maxLength);
    }
}
