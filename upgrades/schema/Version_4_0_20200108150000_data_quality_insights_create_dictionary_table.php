<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20200108150000_data_quality_insights_create_dictionary_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_data_quality_insights_text_checker_dictionary (
    locale_code VARCHAR(20) NOT NULL,
    word VARCHAR(250) NOT NULL,
    INDEX pimee_data_quality_insights_text_checker_dictionary_word_index (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
