<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class Version_7_0_20220629142647_dqi_update_pk_on_product_score_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220629142647_dqi_update_pk_on_product_score';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_checks_that_evaluated_at_is_no_longer_a_primary_key(): void
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertNotContains('evaluated_at', $this->getPrimaryKeyColumnName());
    }

    private function getPrimaryKeyColumnName(): array
    {
        $indexes = $this->get('database_connection')->getSchemaManager()->listTableIndexes('pim_data_quality_insights_product_score');
        $this->assertArrayHasKey('primary', $indexes);
        $pkIndex = $indexes['primary'];

        return $pkIndex->getColumns();
    }
}
