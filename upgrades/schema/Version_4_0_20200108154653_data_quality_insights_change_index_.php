<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_4_0_20200108154653_data_quality_insights_change_index_ extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<SQL
ALTER TABLE pimee_data_quality_insights_criteria_evaluation 
    DROP INDEX data_quality_insights_evaluation_pending_uniqueness, 
    DROP INDEX data_quality_insights_evaluation_status_index,
    ADD UNIQUE INDEX evaluation_pending_uniqueness (product_id, criterion_code, pending),
    ADD INDEX status_index (status),
    ADD INDEX created_at_index (created_at);
    
ALTER TABLE pimee_data_quality_insights_product_axis_rates
    ADD INDEX evaluated_at_index (evaluated_at);
    
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary
    DROP INDEX pimee_data_quality_insights_text_checker_dictionary_word_index,
    ADD INDEX word_index (word);
SQL
        );

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
