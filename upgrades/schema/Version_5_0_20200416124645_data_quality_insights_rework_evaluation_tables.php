<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200416124645_data_quality_insights_rework_evaluation_tables extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $removeDeprecatedProductEvaluations = <<<SQL
DELETE old_evaluations
FROM pimee_data_quality_insights_criteria_evaluation AS old_evaluations
INNER JOIN pimee_data_quality_insights_criteria_evaluation AS younger_evaluations
    ON younger_evaluations.product_id = old_evaluations.product_id
    AND younger_evaluations.criterion_code = old_evaluations.criterion_code
    AND younger_evaluations.created_at > old_evaluations.created_at;
SQL;

        $this->addSql($removeDeprecatedProductEvaluations);

        $reworkProductEvaluationTable = <<<SQL
ALTER TABLE pimee_data_quality_insights_criteria_evaluation
    DROP INDEX evaluation_pending_uniqueness,
    DROP INDEX created_at_index,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY product_criterion (product_id, criterion_code),
    DROP COLUMN id,
    DROP COLUMN pending,
    DROP COLUMN started_at,
    DROP COLUMN ended_at,
    CHANGE COLUMN criterion_code criterion_code varchar(40) NOT NULL AFTER product_id,
    CHANGE COLUMN created_at evaluated_at datetime NULL,
    RENAME AS pimee_data_quality_insights_product_criteria_evaluation;
SQL;

        $this->addSql($reworkProductEvaluationTable);

        $removeDeprecatedProductModelEvaluations = <<<SQL
DELETE old_evaluations
FROM pimee_data_quality_insights_product_model_criteria_evaluation AS old_evaluations
INNER JOIN pimee_data_quality_insights_product_model_criteria_evaluation AS younger_evaluations
    ON younger_evaluations.product_id = old_evaluations.product_id
    AND younger_evaluations.criterion_code = old_evaluations.criterion_code
    AND younger_evaluations.created_at > old_evaluations.created_at
SQL;

        $this->addSql($removeDeprecatedProductModelEvaluations);

        $reworkProductModelEvaluationTable = <<<SQL
ALTER TABLE pimee_data_quality_insights_product_model_criteria_evaluation
    DROP INDEX evaluation_pending_uniqueness,
    DROP INDEX created_at_index,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY product_criterion (product_id, criterion_code),
    DROP COLUMN id,
    DROP COLUMN pending,
    DROP COLUMN started_at,
    DROP COLUMN ended_at,
    CHANGE COLUMN criterion_code criterion_code varchar(40) NOT NULL AFTER product_id,
    CHANGE COLUMN created_at evaluated_at datetime NULL;
SQL;

        $this->addSql($reworkProductModelEvaluationTable);
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
