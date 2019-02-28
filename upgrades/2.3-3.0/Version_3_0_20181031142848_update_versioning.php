<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_0_20181031142848_update_versioning extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $versionEntitiesNameFromTo = [
            'PimEnterprise\\Component\\ProductAsset\\Model\\Asset' => 'Akeneo\\Asset\\Component\\Model\\Asset',
            'PimEnterprise\\Component\\ProductAsset\\Model\\Category' => 'Akeneo\\Asset\\Component\\Model\\Category',
            'PimEnterprise\\Component\\Workflow\\Model\\PublishedProduct' => 'Akeneo\\Pim\\WorkOrganization\\Workflow\\Component\\Model\\PublishedProduct',
        ];

        $updateSql = 'UPDATE pim_versioning_version SET resource_name = :after WHERE id BETWEEN :low_range AND :high_range AND resource_name = :before';
        $preparedStatement = $this->connection->prepare($updateSql);
        $maxId = $this->connection->fetchColumn("SELECT MAX(id) FROM pim_versioning_version");

        $rangeSize = 100000;
        foreach ($versionEntitiesNameFromTo as $before => $after) {
            echo "Starting migration of {$before} to {$after}\n";
            $offset = 0;
            $limit = $rangeSize;
            while ($offset < $maxId) {
                echo "The MAX(id) is {$maxId}, current range from {$offset} to {$limit}\n";
                $preparedStatement->execute(['before' => $before, 'after' => $after, 'low_range' => $offset, 'high_range' => $limit]);
                echo "Done\n";
                $offset = $limit;
                $limit += $rangeSize;
            }
        }

        /**
         * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
         * sql query has been made to the db during the migration process.
         */
        $this->addSql('SELECT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
