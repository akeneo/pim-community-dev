<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_6_0_20210507154610_update_pim_catalog_completeness_id_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20210507154610_update_pim_catalog_completeness_id';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_completeness_id_has_changed_to_bigint(): void
    {
        $this->ensureCompletenessUsesNormalInt();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $sql = <<<SQL
select data_type
from information_schema.COLUMNS
where
  TABLE_NAME='pim_catalog_completeness'
  and COLUMN_NAME='id';
SQL;
        $result = $this->get('database_connection')
            ->executeQuery($sql)
            ->fetchOne();

        $this->assertEquals('bigint', $result);
    }

    private function ensureCompletenessUsesNormalInt(): void
    {
        $this->get('database_connection')->executeQuery(
            "ALTER TABLE pim_catalog_completeness MODIFY id int AUTO_INCREMENT"
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
