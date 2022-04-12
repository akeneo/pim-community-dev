<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration modify the charset of the word column of the pimee_data_quality_insights_text_checker_dictionary table
 */
final class Version_7_0_20220330160000_dqi_modify_word_column_collation extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->alreadyModified($schema), 'The pimee_data_quality_insights_text_checker_dictionary.word column is already modified');

        $query = <<<SQL
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary 
MODIFY COLUMN word VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;
SQL;

        $this->addSql($query);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function alreadyModified(Schema $schema): bool
    {
        $query = <<<SQL
SELECT CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.`COLUMNS`
WHERE TABLE_SCHEMA = :table_schema
AND TABLE_NAME = 'pimee_data_quality_insights_text_checker_dictionary'
AND COLUMN_NAME = 'word';
SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchAssociative();

        return 'utf8mb4' === $result['CHARACTER_SET_NAME'] && 'utf8mb4_bin' === $result['COLLATION_NAME'];
    }
}
