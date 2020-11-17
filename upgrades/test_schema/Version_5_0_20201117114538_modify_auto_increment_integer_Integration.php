<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_5_0_20201117114538_modify_auto_increment_integer_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201117114538_modify_auto_increment_integer';

    public function test_completeness_id_has_changed_to_bigint(): void
    {
        $this->ensureCompletenessUsesNormalInt();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $tableColumns = $this->schemaManager->listTableColumns('pim_catalog_completeness');

        $found = false;
        foreach($tableColumns as $column) {
            if ($column->getName() === 'id') {
                $this->assertEquals('bigint', $column->getType());
                $found=true;
            }
        }

        $this->assertTrue($found); // ← we need to ensure there is a ID column hence we have tested it correctly
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
