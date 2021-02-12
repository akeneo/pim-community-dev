<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210118091114_dqi_add_uniqueness_constraint_on_dictionary extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
RENAME TABLE pimee_data_quality_insights_text_checker_dictionary TO pimee_data_quality_insights_dictionary_depr;

CREATE TABLE pimee_data_quality_insights_text_checker_dictionary (
    id INT AUTO_INCREMENT NOT NULL,
    locale_code VARCHAR(20) NOT NULL,
    word VARCHAR(250) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE unique_locale_word (locale_code, word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word) 
SELECT locale_code, word FROM pimee_data_quality_insights_dictionary_depr;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
