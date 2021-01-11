<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_0_20191227141338_fix_versioning_indexes extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->updateVersioningIndexes();
    }

    /**
     * Try to drop and create versioning indexes.
     * Does nothing if they are already dropped/created
     *
     * @throws DriverException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateVersioningIndexes()
    {
        $sql = 'ALTER TABLE pim_versioning_version DROP INDEX resource_name_idx;';

        try {
            $this->connection->executeQuery($sql);
        } catch (DriverException $dbalException) {
            if ($dbalException->getErrorCode() !== 1091) {
                throw $dbalException;
            }
        }

        $sql = 'ALTER TABLE pim_versioning_version DROP INDEX resource_name_resource_id_idx';

        try {
            $this->connection->executeQuery($sql);
        } catch (DriverException $dbalException) {
            if ($dbalException->getErrorCode() !== 1091) {
                throw $dbalException;
            }
        }

        $sql = <<<SQL
ALTER TABLE pim_versioning_version 
    ADD INDEX resource_name_resource_id_version_idx (resource_name, resource_id, version);
SQL;

        try {
            $this->connection->executeQuery($sql);
        } catch (DriverException $dbalException) {
            if ($dbalException->getErrorCode() !== 1061) {
                throw $dbalException;
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
