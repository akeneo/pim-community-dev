<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200121085532_remove_user_email_notifications extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
ALTER TABLE oro_user DROP COLUMN emailNotifications;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
