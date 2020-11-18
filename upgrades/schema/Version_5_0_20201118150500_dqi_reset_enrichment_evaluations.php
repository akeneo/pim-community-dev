<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201118150500_dqi_reset_enrichment_evaluations extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET status = 'pending'
WHERE criterion_code IN('completeness_of_non_required_attributes', 'completeness_of_required_attributes');
SQL);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
