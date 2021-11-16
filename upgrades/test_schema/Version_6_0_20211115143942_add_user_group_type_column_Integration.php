<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211115143942_add_user_group_type_column_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211115143942_add_user_group_type_column';

    private Connection $connection;

    public function test_it_adds_a_new_type_column_to_the_oro_access_group_table(): void
    {
        $this->dropTypeIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->typeColumnExists());

        $allUserGroupTypes = $this->getAllUserGroupTypes();

        Assert::assertEquals([
            'IT support' => 'default',
            'Manager' => 'default',
            'Redactor' => 'default',
            'All' => 'default',
        ], $allUserGroupTypes);
    }

    private function dropTypeIfExists(): void
    {
        if ($this->typeColumnExists()) {
            $this->connection->executeQuery('ALTER TABLE oro_access_group DROP COLUMN type;');
        }

        Assert::assertEquals(false, $this->typeColumnExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    private function typeColumnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('oro_access_group');

        return isset($columns['type']);
    }

    private function getAllUserGroupTypes(): array
    {
        $query = <<<SQL
SELECT name, type
FROM oro_access_group
SQL;
        $data = $this->connection->executeQuery($query)->fetchAll();

        return array_column($data, 'type', 'name');
    }
}
