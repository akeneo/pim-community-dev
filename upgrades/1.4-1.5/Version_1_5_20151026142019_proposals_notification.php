<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration adds two new columns to the User entity :
 *  - "proposalsToReviewNotification" -> Does the user want to be notified of new proposals to review ?
 *  - "proposalsStateNotification"    -> Does the user want to be notified when its proposals are accepted or rejected ?
 * For those two columns, default is "1" (true)
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class Version_1_5_20151026142019_proposals_notification extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE oro_user ADD proposalsToReviewNotification TINYINT(1) DEFAULT \'1\' NOT NULL, ADD proposalsStateNotification TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
