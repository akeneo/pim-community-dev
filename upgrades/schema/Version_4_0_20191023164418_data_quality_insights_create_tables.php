<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\Query\InitDataQualityInsightsSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version_4_0_20191023164418_data_quality_insights_create_tables extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(InitDataQualityInsightsSchema::QUERY);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS pimee_data_quality_insights_criteria_evaluation');
        $this->addSql('DROP TABLE IF EXISTS pimee_data_quality_insights_product_axis_rates');
        $this->addSql('DROP TABLE IF EXISTS pimee_data_quality_insights_dashboard_rates_projection');
    }
}
