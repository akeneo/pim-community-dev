<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;


/**
 * Remove triggers added in the UUID migration which add triggers on foreign uuid column.
 * @see https://github.com/akeneo/pim-community-dev/blob/77be7d26721554834bbbabae39bf6f11a90f77ac/src/Akeneo/Pim/Enrichment/Bundle/Command/MigrateToUuid/MigrateToUuidAddTriggers.php#L15
 */
final class Version_7_0_20220728121643_remove_uuid_triggers_Integration extends TestCase
{
    use ExecuteMigrationTrait;
    private const MIGRATION_LABEL = '_7_0_20220728121643_remove_uuid_triggers';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_removes_the_triggers(): void
    {
        $this->createDummyTriggersToBeRemoved();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTriggersRemoved();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createDummyTriggersToBeRemoved(): void
    {
        $createDummyTriggerQuery = <<<SQL
Create Trigger {trigger_name}
BEFORE INSERT ON pim_catalog_category_product FOR EACH ROW  
BEGIN  
IF NEW.product_uuid IS NULL THEN SET NEW.category_id = 1;  
END IF;  
END
SQL;
        foreach(Version_7_0_20220728121643_remove_uuid_triggers::TRIGGERS_TO_REMOVE as $triggerName) {
            $query = \strtr($createDummyTriggerQuery, ['{trigger_name}' => $triggerName]);
            $this->connection->executeStatement($query);
        }
    }

    private function assertTriggersRemoved(): void
    {
        $stmt = $this->connection->executeQuery('SHOW TRIGGERS;');
        $result = $stmt->fetchFirstColumn();
        $this->assertEmpty($result);
    }
}
