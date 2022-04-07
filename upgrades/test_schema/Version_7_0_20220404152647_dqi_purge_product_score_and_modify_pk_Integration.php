<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class Version_7_0_20220404152647_dqi_purge_product_score_and_modify_pk_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220404152647_dqi_purge_product_score_and_modify_pk';

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
        $query = <<<SQL
SELECT COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'pim_data_quality_insights_product_score'
AND CONSTRAINT_NAME = 'PRIMARY';
SQL;

        $columnNames = $this->get('database_connection')->executeQuery($query)->fetchAllAssociative();

        return array_map(
            fn (array $result): string => $result['COLUMN_NAME'],
            $columnNames
        );
    }
}
