<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_7_0_20220414134510_add_dqi_scores_partial_criteria_column_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220414134510_add_dqi_scores_partial_criteria_column';

    protected function getConfiguration()
    {
        return null;
    }

    public function test_id_adds_partial_criteria_column(): void
    {
        foreach (['pim_data_quality_insights_product_score', 'pim_data_quality_insights_product_model_score'] as $tableName) {
            $this->removePartialCriteriaColumn($tableName);
        }

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        foreach (['pim_data_quality_insights_product_score', 'pim_data_quality_insights_product_model_score'] as $tableName) {
            $this->assertTrue($this->scoresPartialCriteriaColumnExists($tableName));
        }
    }

    private function scoresPartialCriteriaColumnExists(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'scores_partial_criteria'
            SQL,
        );

        return count($rows) >= 1;
    }

    private function removePartialCriteriaColumn(string $tableName): void
    {
        if (!$this->scoresPartialCriteriaColumnExists($tableName)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE $tableName DROP COLUMN scores_partial_criteria;
            SQL
        );
    }
}
