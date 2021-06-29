<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201208134018_dqi_drop_deprecated_tables extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
DROP TABLE IF EXISTS pim_data_quality_insights_product_axis_rates;
DROP TABLE IF EXISTS pim_data_quality_insights_product_model_axis_rates;
DROP TABLE IF EXISTS pim_data_quality_insights_dashboard_rates_projection;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
