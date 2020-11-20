<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20201117114538_modify_auto_increment_integer_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201117114538_modify_auto_increment_integer';

    /** @var AbstractSchemaManager */
    private $schemaManager;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_completeness_id_has_changed_to_bigint(): void
    {
        $this->ensureCompletenessUsesNormalInt();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $tableColumns = $this->get('database_connection')
            ->getSchemaManager()
            ->listTableColumns('pim_catalog_completeness');

        $sql = <<<SQL
select data_type
from information_schema.COLUMNS
where
  TABLE_NAME='pim_catalog_completeness'
  and COLUMN_NAME='id';
SQL;
        $result = $this->get('database_connection')
            ->executeQuery($sql)
            ->fetchColumn();

        $this->assertEquals('bigint', $result);
    }

    private function ensureCompletenessUsesNormalInt(): void {
        $sql = "ALTER TABLE pim_catalog_completeness MODIFY id int AUTO_INCREMENT";
        $this->get('database_connection')->executeQuery($sql);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
