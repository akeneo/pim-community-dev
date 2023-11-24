<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20220801160000_modify_metric_family_and_default_metric_unit extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->columnsAreModified($schema)) {
            $this->write('metric_family and default_metric_unit columns are already modified');

            return;
        }

        $this->addSql('ALTER TABLE pim_catalog_attribute MODIFY COLUMN metric_family VARCHAR(100), MODIFY COLUMN default_metric_unit VARCHAR(100)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnsAreModified(Schema $schema): bool
    {
        $sql = <<<SQL
            SELECT DISTINCT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = :table_schema AND TABLE_NAME = 'pim_catalog_attribute' AND COLUMN_NAME IN ('metric_family', 'default_metric_unit')
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'table_schema' => $schema->getName()
        ])->fetchAll(\PDO::FETCH_ASSOC);

        return 100 === (int) $result[0]['CHARACTER_MAXIMUM_LENGTH'] && 100 === (int) $result[1]['CHARACTER_MAXIMUM_LENGTH'];
    }
}
