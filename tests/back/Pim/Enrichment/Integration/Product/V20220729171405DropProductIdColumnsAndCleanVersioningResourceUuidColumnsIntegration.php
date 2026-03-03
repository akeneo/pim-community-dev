<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumnsIntegration extends TestCase
{
    private ?int $productVersionId = null;
    private ?int $nonProductVersionId = null;

    private readonly Connection $connection;
    private readonly V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns $migrationToTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->migrationToTest = $this->get(V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns::class);
        $this->productVersionId = null;
        $this->nonProductVersionId = null;
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        foreach ($this->migrationToTest::TABLES_TO_UPDATE as $tableName => $properties) {
            foreach ($properties['triggers'] as $triggerName) {
                $this->connection->executeStatement(
                    'DROP TRIGGER IF EXISTS ' . $triggerName
                );
            }
        }
        parent::tearDown();
    }

    public function test_it_empties_the_resource_uuid_when_the_version_is_not_product_related()
    {
        $this->createDummyTriggersToBeRemoved();
        $this->createDummyColumnsToBeRemoved();
        $this->createProductRelatedVersionWithResourceUuid();
        $this->createNonProductRelatedVersionWithResourceUuid();

        $this->migrationToTest->migrate();

        $this->assertColumnsRemoved();
        $this->assertTriggersRemoved();
        $this->assertProductRelatedVersionHasResourceUuid();
        $this->assertNonProductRelatedVersionHasNoResourceUuid();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProductRelatedVersionWithResourceUuid(): void
    {
        $sql = <<<SQL
INSERT INTO `pim_versioning_version` (`author`, `resource_name`, `resource_id`, `resource_uuid`, `snapshot`, `changeset`, `context`, `version`, `logged_at`, `pending`)
VALUES
	('system', :resource_name, NULL, 'A_UUID', 'a:11:{s:6:\"family\";s:16:\"multifunctionals\";s:6:\"groups\";s:0:\"\";s:10:\"categories\";s:41:\"lexmark,multifunctionals,print_scan_sales\";s:6:\"parent\";s:0:\"\";s:14:\"color_scanning\";s:1:\"0\";s:23:\"description-en_US-print\";s:1477:\"<b>Streamlined Reliability</b>\\nA smart, reliable option for bringing duplex printing, copying, scanning and high-speed faxing into one machine, with up to 40 ppm and the ability to scan documents straight to e-mail or a flash drive.\\n\\n<b>As Easy as It Gets</b>\\nRight out of the box, you’ll power through tasks at exceptionally fast speeds—up to 40 ppm. It’s a breeze to set up, install, and start enjoying the benefit of doing all those multiple tasks on one user-friendly machine.\\n\\n<b>Smarter Printer, Smarter Business</b>\\nThe large LCD color touch screen offers amazingly simple access to a rich range of features, including duplex scanning, advanced copying and easy user authorization for enhanced security. You can even customize the touch screen to meet your workgroup’s specific needs.\\n\\n<b>Small in Size, Huge on Features</b>\\nEnjoy an intelligent, efficient combination of built-in features like duplex printing, copying and scanning plus a front Direct USB port. Gives you the ability to scan to multiple destinations, letting your workgroup breeze through intense workloads.\\n\\n<b>Save the Earth and Money</b>\\nLower your cost per page while helping conserve resources with up to 9,000*-page or 15,000*-page replacement cartridges. Add that to the automatic duplex printing and the energy savings of consolidating to one smart device, and you’re taking big steps toward an eco-conscious workplace. (*Declared yield in accordance with ISO/IEC 19752.)\";s:18:\"maximum_print_size\";s:19:\"legal_216_x_356_mm_\";s:4:\"name\";s:14:\"Lexmark X464de\";s:22:\"release_date-ecommerce\";s:25:\"2012-04-20T00:00:00+00:00\";s:3:\"sku\";s:8:\"13871461\";s:7:\"enabled\";i:1;}', 'a:9:{s:6:\"family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"multifunctionals\";}s:10:\"categories\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:41:\"lexmark,multifunctionals,print_scan_sales\";}s:14:\"color_scanning\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:1:\"0\";}s:23:\"description-en_US-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:1477:\"<b>Streamlined Reliability</b>\\nA smart, reliable option for bringing duplex printing, copying, scanning and high-speed faxing into one machine, with up to 40 ppm and the ability to scan documents straight to e-mail or a flash drive.\\n\\n<b>As Easy as It Gets</b>\\nRight out of the box, you’ll power through tasks at exceptionally fast speeds—up to 40 ppm. It’s a breeze to set up, install, and start enjoying the benefit of doing all those multiple tasks on one user-friendly machine.\\n\\n<b>Smarter Printer, Smarter Business</b>\\nThe large LCD color touch screen offers amazingly simple access to a rich range of features, including duplex scanning, advanced copying and easy user authorization for enhanced security. You can even customize the touch screen to meet your workgroup’s specific needs.\\n\\n<b>Small in Size, Huge on Features</b>\\nEnjoy an intelligent, efficient combination of built-in features like duplex printing, copying and scanning plus a front Direct USB port. Gives you the ability to scan to multiple destinations, letting your workgroup breeze through intense workloads.\\n\\n<b>Save the Earth and Money</b>\\nLower your cost per page while helping conserve resources with up to 9,000*-page or 15,000*-page replacement cartridges. Add that to the automatic duplex printing and the energy savings of consolidating to one smart device, and you’re taking big steps toward an eco-conscious workplace. (*Declared yield in accordance with ISO/IEC 19752.)\";}s:18:\"maximum_print_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"legal_216_x_356_mm_\";}s:4:\"name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Lexmark X464de\";}s:22:\"release_date-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"2012-04-20T00:00:00+00:00\";}s:3:\"sku\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"13871461\";}s:7:\"enabled\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}}', NULL, 1, '2022-07-27 16:20:49', 0);
SQL;
        $this->connection->executeStatement($sql, ['resource_name' => Product::class]);
        $this->productVersionId = (int)$this->connection->lastInsertId();
    }

    private function createNonProductRelatedVersionWithResourceUuid(): void
    {
        $sql = <<<SQL
INSERT INTO `pim_versioning_version` (`author`, `resource_name`, `resource_id`, `resource_uuid`, `snapshot`, `changeset`, `context`, `version`, `logged_at`, `pending`)
VALUES
	('system', 'Akeneo\\Channel\\Infrastructure\\Component\\Model\\Locale', '6', 'A_UUID', 'a:3:{s:4:\"code\";s:5:\"ar_EG\";s:15:\"view_permission\";s:0:\"\";s:15:\"edit_permission\";s:0:\"\";}', 'a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}', NULL, 1, '2022-07-27 16:20:26', 0);
SQL;
        $this->connection->executeStatement($sql);
        $this->nonProductVersionId = (int)$this->connection->lastInsertId();
    }

    private function assertProductRelatedVersionHasResourceUuid(): void
    {
        $result = $this->fetchResourceIdForVersion($this->productVersionId);
        $this->assertNotNull($result);
    }

    private function assertNonProductRelatedVersionHasNoResourceUuid(): void
    {
        $result = $this->fetchResourceIdForVersion($this->nonProductVersionId);
        $this->assertNull($result);
    }

    private function fetchResourceIdForVersion(int $versionId): ?string
    {
        $stmt = $this->connection->executeQuery(
            'SELECT resource_uuid FROM pim_versioning_version WHERE id = :version_id',
            ['version_id' => $versionId]
        );

        return $stmt->fetchOne();
    }

    private function createDummyTriggersToBeRemoved(): void
    {
        $createDummyTriggerQuery = <<<SQL
        DROP TRIGGER IF EXISTS %s;
        CREATE TRIGGER %s
        BEFORE INSERT ON pim_catalog_category_product FOR EACH ROW  
        BEGIN  
            IF NEW.product_uuid IS NULL THEN SET NEW.category_id = 1;  
            END IF;  
        END
        SQL;
        foreach($this->migrationToTest::TABLES_TO_UPDATE as $tableName => $properties) {
            if (!$this->connection->getSchemaManager()->tablesExist([$tableName])) {
                continue;
            }
            foreach ($properties['triggers'] as $triggerName) {
                $query = \sprintf($createDummyTriggerQuery, $triggerName, $triggerName);
                $this->connection->executeStatement($query);
            }
        }
    }

    private function assertTriggersRemoved(): void
    {
        $stmt = $this->connection->executeQuery('SHOW TRIGGERS;');
        $result = $stmt->fetchFirstColumn();
        $this->assertEmpty($result);
    }

    private function assertColumnsRemoved(): void
    {
        foreach ($this->migrationToTest::TABLES_TO_UPDATE as $table => $properties) {
            $productIdColumnName = $properties['column'];
            if (null === $productIdColumnName) {
                continue;
            }
            $tableColumnNames = \array_map(
                static fn(Column $column) => $column->getName(),
                $this->connection->getSchemaManager()->listTableColumns($table)
            );
            $this->assertNotContains(
                $productIdColumnName,
                $tableColumnNames,
                \sprintf('Expected column "%s" to not exist on table "%s". Column found.', $productIdColumnName, $table)
            );
        }
    }

    private function createDummyColumnsToBeRemoved(): void
    {
        $addColumnQuery = <<<SQL
ALTER TABLE %s
    ADD COLUMN %s INT,
    ADD COLUMN dummy INT,
    ADD CONSTRAINT UNIQUE (%s, dummy),
    ADD CONSTRAINT FOREIGN KEY(%s) REFERENCES pim_catalog_product(id);
SQL;
        foreach ($this->migrationToTest::TABLES_TO_UPDATE as $table => $properties) {
            if ($this->connection->getSchemaManager()->tablesExist($table) && null !== $properties['column']) {
                $query = \sprintf($addColumnQuery, $table, $properties['column'], $properties['column'], $properties['column']);
                $this->connection->executeStatement($query);
            }
        }
    }

    private function cleanDatabase(): void
    {
        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $consoleApp = new Application($kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--full-database' => true,
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);

        $input = new ArrayInput([
            'command' => 'pim:installer:db',
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);
    }
}
