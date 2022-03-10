<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220221151255_data_quality_insights_purge_attribute_quality extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Purge table pimee_dqi_attribute_locale_quality and pimee_dqi_attribute_quality and add foreign key on this same tables';
    }

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        if (!$this->foreignKeyExistsOnTable('FK_pimeedqi_attribute_locale_quality_as_code', 'pimee_dqi_attribute_locale_quality')) {
            $this->addSql(<<<SQL
            DELETE dqi FROM pimee_dqi_attribute_locale_quality as dqi
            LEFT JOIN pim_catalog_attribute a ON a.code = dqi.attribute_code
            WHERE a.id IS NULL;
            SQL
            );
            $this->addSql(<<<SQL
            ALTER TABLE pimee_dqi_attribute_locale_quality
            ADD CONSTRAINT FK_pimeedqi_attribute_locale_quality_as_code
            FOREIGN KEY (attribute_code)
            REFERENCES pim_catalog_attribute(code)
            ON DELETE CASCADE;
            SQL
            );
        }

        if (!$this->foreignKeyExistsOnTable('FK_pimeedqi_attribute_quality_as_code', 'pimee_dqi_attribute_quality')) {
            $this->addSql(<<<SQL
            DELETE dqi FROM pimee_dqi_attribute_quality as dqi
            LEFT JOIN pim_catalog_attribute a ON a.code = dqi.attribute_code
            WHERE a.id IS NULL;
            SQL
            );

            $this->addSql(<<<SQL
            ALTER TABLE pimee_dqi_attribute_quality
            ADD CONSTRAINT FK_pimeedqi_attribute_quality_as_code
            FOREIGN KEY (attribute_code)
            REFERENCES pim_catalog_attribute (code)
            ON DELETE CASCADE
            SQL
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function foreignKeyExistsOnTable(string $foreignKeyName, string $tableName): bool
    {
        $sql = <<<SQL
        SELECT EXISTS(
            SELECT CONSTRAINT_NAME
            FROM information_schema.key_column_usage
            WHERE table_name = :table_name
              AND CONSTRAINT_NAME = :foreign_key_name
              AND table_schema = DATABASE()
              AND REFERENCED_COLUMN_NAME is not NULL
        ) as is_existing
        SQL;
        $params = [
            'table_name' => $tableName,
            'foreign_key_name' => $foreignKeyName,
        ];

        return (bool) $this->connection->executeQuery($sql, $params)->fetchOne();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
