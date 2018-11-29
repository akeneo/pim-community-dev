<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrates the user to the version with properties in json instead of override the CE for EE needs
 */
class Version_3_0_20181128123105_migrate_users extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $users = $this->connection->fetchAll('SELECT * FROM oro_user');

        foreach ($users as $user) {
            $properties = [];
            $assetCategoryCode = $this->connection->fetchColumn(sprintf('SELECT code from pimee_product_asset_category WHERE id = %s', $user['defaultAssetTree_id']));
            $properties['default_asset_tree'] = $assetCategoryCode;
            $properties['asset_delay_reminder'] = $user['assetDelayReminder'];
            $properties['proposals_to_review_notification'] = (bool) $user['proposalsToReviewNotification'];
            $properties['proposals_state_notifications'] = (bool) $user['proposalsStateNotification'];

            $this->addSQL(sprintf('UPDATE oro_user SET properties = \'%s\' WHERE id = %s', json_encode($properties), $user['id']));
        }

        $defaultAssetTreeConstraint = $this->connection->fetchAll("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'oro_user' AND REFERENCED_TABLE_NAME = 'pimee_product_asset_category'");
        $defaultAssetTreeConstraintName = $defaultAssetTreeConstraint[0]['CONSTRAINT_NAME'];

        $this->addSQL(<<<SQL
            ALTER TABLE oro_user
            DROP FOREIGN KEY $defaultAssetTreeConstraintName,   
            DROP COLUMN assetDelayReminder ,
            DROP COLUMN defaultAssetTree_id,
            DROP COLUMN proposalsToReviewNotification,
            DROP COLUMN proposalsStateNotification
SQL
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
