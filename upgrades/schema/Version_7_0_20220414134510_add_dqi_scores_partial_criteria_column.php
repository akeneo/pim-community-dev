<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220414134510_add_dqi_scores_partial_criteria_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add DQI scores with partial criteria columns';
    }

    public function up(Schema $schema): void
    {
        foreach (['pim_data_quality_insights_product_score', 'pim_data_quality_insights_product_model_score'] as $tableName) {
            if (!$this->scoresPartialCriteriaColumnExists($tableName)) {
                $this->addPartialCriteriaColumn($tableName);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function addPartialCriteriaColumn(string $tableName): void
    {
        $this->addSql(
            <<<SQL
                ALTER TABLE $tableName ADD scores_partial_criteria JSON DEFAULT NULL;
            SQL
        );
    }

    private function scoresPartialCriteriaColumnExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'scores_partial_criteria'
            SQL,
        );

        return count($rows) >= 1;
    }
}
