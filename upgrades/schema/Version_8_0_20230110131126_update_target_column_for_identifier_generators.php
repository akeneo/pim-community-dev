<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration:
 * - Adds a target_id column
 * - Sets the attribute_id in this new column from previous target
 * - Adds a foreign constraint
 * - Removes target column
 *
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230110131126_update_target_column_for_identifier_generators extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates the target column in identifier generator table';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('pim_catalog_identifier_generator', 'target')) {
            return;
        }

        $sql = <<<SQL
ALTER TABLE pim_catalog_identifier_generator ADD COLUMN target_id INT NOT NULL AFTER target;
UPDATE pim_catalog_identifier_generator SET target_id=(SELECT id FROM pim_catalog_attribute WHERE code=target);
ALTER TABLE pim_catalog_identifier_generator ADD CONSTRAINT `pim_catalog_identifier_generator_ibfk_1` FOREIGN KEY(target_id) REFERENCES pim_catalog_attribute(id) ON DELETE CASCADE;
ALTER TABLE pim_catalog_identifier_generator DROP COLUMN target;

ALTER TABLE pim_catalog_identifier_generator ADD COLUMN options JSON NOT NULL AFTER delimiter;
UPDATE pim_catalog_identifier_generator SET options=JSON_OBJECT('delimiter', delimiter, 'text_transformation', 'no');
ALTER TABLE pim_catalog_identifier_generator DROP COLUMN delimiter;
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }
}
