<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_6_0_20210914155210_update_pim_catalog_completeness_id_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210914155210_update_pim_catalog_completeness_id';

    public function test_completeness_id_has_changed_to_bigint(): void
    {
        $this->ensureCompletenessUsesNormalInt();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertCompletenessUsesBigInt();
    }

    private function ensureCompletenessUsesNormalInt(): void
    {
        $this->getConnection()->executeQuery("ALTER TABLE pim_catalog_completeness MODIFY id int AUTO_INCREMENT");
    }

    private function assertCompletenessUsesBigInt(): void
    {
        $sql = <<<SQL
            SELECT data_type
            FROM information_schema.COLUMNS
            WHERE table_name = 'pim_catalog_completeness'
            AND column_name = 'id';
        SQL;

        $result = $this->getConnection()->executeQuery($sql)->fetchColumn();

        $this->assertEquals('bigint', $result);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
