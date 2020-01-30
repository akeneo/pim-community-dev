<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\User\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\CreateUser;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\UpdateUserPermissions;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateUserPermissionsIntegration extends TestCase
{
    public function test_it_updates_a_role_and_a_group()
    {
        $connection = $this->createConnection('pimgento');

        $userId = $this->fetchConnectionUserId($connection->username());
        $this->assertConnectionRole($userId, (int)$connection->userRoleId());
        Assert::assertNull($connection->userGroupId());

        $newRoleId = $this->fetchNewRoleId((int) $connection->userRoleId());
        $newGroupId = $this->fetchNewGroupId((int) $connection->userGroupId());

        $this->getUpdateUserPermissionsService()->execute(new UserId($userId), $newRoleId, $newGroupId);

        $this->assertConnectionRole($userId, $newRoleId);
        $this->assertConnectionGroup($userId, $newGroupId);
    }

    private function fetchConnectionUserId(string $username): int
    {
        $sqlQuery = <<<SQL
SELECT id FROM oro_user WHERE username = :username
SQL;

        return (int) $this->getDatabaseConnection()->fetchColumn($sqlQuery, ['username' => $username]);
    }

    private function assertConnectionRole(int $userId, int $userRoleId): void
    {
        $sqlQuery = <<<SQL
SELECT COUNT(1) FROM oro_user_access_role WHERE user_id = :user_id AND role_id = :role_id
SQL;
        $rolesCount = $this
            ->getDatabaseConnection()
            ->fetchColumn($sqlQuery, ['user_id' => $userId, 'role_id' => $userRoleId]);
        Assert::assertEquals(1, $rolesCount);
    }

    private function assertConnectionGroup(int $userId, int $userGroupId): void
    {
        $sqlQuery = <<<SQL
SELECT COUNT(1) FROM oro_user_access_group WHERE user_id = :user_id AND group_id = :group_id
SQL;
        $groupsCount = $this
            ->getDatabaseConnection()
            ->fetchColumn($sqlQuery, ['user_id' => $userId, 'group_id' => $userGroupId]);
        Assert::assertEquals(1, $groupsCount);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getUpdateUserPermissionsService(): UpdateUserPermissions
    {
        return $this->get('akeneo_connectivity.connection.service.user.update_user_permissions');
    }

    private function createConnection(string $code): ConnectionWithCredentials
    {
        $command = new CreateConnectionCommand($code, $code, FlowType::OTHER);

        return $this
            ->get('akeneo_connectivity.connection.application.handler.create_connection')
            ->handle($command);
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function fetchNewRoleId(int $currentRoleId): int
    {
        return (int) $this
            ->getDatabaseConnection()
            ->fetchColumn('SELECT id FROM oro_access_role where id != :role_id', ['role_id' => $currentRoleId]);
    }

    private function fetchNewGroupId(int $currentGroupId): int
    {
        return (int) $this
            ->getDatabaseConnection()
            ->fetchColumn('SELECT id FROM oro_access_group where id != :group_id', ['group_id' => $currentGroupId]);
    }
}
