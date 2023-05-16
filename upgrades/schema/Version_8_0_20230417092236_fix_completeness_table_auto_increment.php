<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230417092236_fix_completeness_table_auto_increment extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets the `id` column of the `pim_catalog_completeness` table to be auto_increment';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $this->isColumnAutoIncremental('pim_catalog_completeness', 'id'),
            'id column is already using auto_increment'
        );

        $this->connection->executeStatement(<<<SQL
ALTER TABLE pim_catalog_completeness MODIFY COLUMN id bigint NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function isColumnAutoIncremental(string $tableName, string $columnName): bool
    {
        $sql = <<<SQL
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = :schema
  AND TABLE_NAME = :tableName
  AND COLUMN_NAME = :columnName
  AND EXTRA like '%auto_increment%'
SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $this->connection->getDatabase(),
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return \intval($result) > 0;
    }
}
