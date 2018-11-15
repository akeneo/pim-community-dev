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
            'PimEnterprise\Component\ProductAsset\Model\Asset' => 'Akeneo\Asset\Component\Model\Asset',
            'PimEnterprise\Component\ProductAsset\Model\Category' => 'Akeneo\Asset\Component\Model\Category',
            'PimEnterprise\Component\Workflow\Model\PublishedProduct' => 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct',
        ];

        $updateSql = 'UPDATE pim_versioning_version SET resource_name = :after WHERE resource_name = :before';
        $preparedStatement = $this->connection->prepare($updateSql);

        foreach ($versionEntitiesNameFromTo as $before => $after) {
            $preparedStatement->execute(['before' => $before, 'after' => $after]);
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
