<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_6_0_20210106090000_dqi_alter_dictionary_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary
	ADD id INT AUTO_INCREMENT NOT NULL,
	ADD PRIMARY KEY (id);
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
