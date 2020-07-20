<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_5_0_20200720083247_drop_title_formatting_tables extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
DROP TABLE IF EXISTS pimee_data_quality_insights_pm_title_formatting_ignore;
DROP TABLE IF EXISTS pimee_data_quality_insights_title_formatting_ignore;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
