<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class AddMissingJobPermissionsForUserGroupAllCommandIntegration extends TestCase
{
    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function itCreatesUserGroupallAndPermissions(): void
    {
        $this->connection->executeQuery('DELETE FROM oro_access_group WHERE name = "All"');
        $this->createAdminUser();

        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('pimee:permission:add-missing-job-permissions-for-user-group');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertUserGroupAllExists();
        $this->assertAdminUserIsInUserGroupAll();
        $this->assertPermissionsAreCreatedForUserGroupAll();
    }

    private function assertUserGroupAllExists(): void
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1 FROM oro_access_group WHERE name = 'All'
          ) as is_existing
SQL;

        $statement = $this->connection->executeQuery($sql);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        self::assertTrue(
            Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform),
            'The user group is not created'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertAdminUserIsInUserGroupAll(): void
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1
              FROM oro_user_access_group ug
                  JOIN oro_user u ON u.id = ug.user_id
                  JOIN oro_access_group g ON g.id = ug.group_id
              WHERE g.name = 'All' AND u.username = 'admin'
          ) as is_existing
SQL;

        $statement = $this->connection->executeQuery($sql);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        self::assertTrue(
            Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform),
            'The "admin" user is not attached to the "All" user group'
        );
    }

    private function assertPermissionsAreCreatedForUserGroupAll(): void
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1
              FROM pimee_security_job_profile_access jpa
                  JOIN oro_access_group g ON g.id = jpa.user_group_id
              WHERE g.name = 'All'
          ) as is_existing
SQL;

        $statement = $this->connection->executeQuery($sql);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        self::assertTrue(
            Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform),
            'No permission found for "All" user group'
        );
    }
}
