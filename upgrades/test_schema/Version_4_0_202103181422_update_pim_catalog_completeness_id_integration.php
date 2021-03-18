<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_4_0_202103181422_update_pim_catalog_completeness_id_integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_4_0_202103181422_update_pim_catalog_completeness_id_integration';

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
            ->fetchColumn();

        $this->assertEquals('bigint', $result);
    }

    private function ensureCompletenessUsesNormalInt(): void
    {
        $sql = "ALTER TABLE pim_catalog_completeness MODIFY id int AUTO_INCREMENT";
        $this->get('database_connection')->executeQuery($sql);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
