<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210101000000_fix_json_columns extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1');
        foreach ($this->getLegacyJsonColumns() as $columnInfo) {
            $this->addSql($this->getAlterSqlQuery($columnInfo));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getLegacyJsonColumns(): array
    {
        return $this->connection->fetchAllAssociative(<<<SQL
            SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
            FROM information_schema.COLUMNS
            WHERE COLUMN_COMMENT = '(DC2Type:json_array)'
            AND TABLE_SCHEMA = :schema
            SQL,
            [
                'schema' => $this->connection->getDatabase()
            ]
        );
    }

    /**
     * @return string ex: "ALTER TABLE akeneo_batch_job_execution MODIFY raw_parameters json not null;"
     */
    private function getAlterSqlQuery(array $columnInfo): string
    {
        return \sprintf(
            'ALTER TABLE %s MODIFY %s %s %s %s;',
            $columnInfo['TABLE_NAME'],
            $columnInfo['COLUMN_NAME'],
            $columnInfo['COLUMN_TYPE'],
            ('NO' === $columnInfo['IS_NULLABLE']) ? 'NOT NULL' : '',
            (null !== $columnInfo['COLUMN_DEFAULT']) ? 'DEFAULT (' . $columnInfo['COLUMN_DEFAULT'] . ')' : ''
        );
    }
}
