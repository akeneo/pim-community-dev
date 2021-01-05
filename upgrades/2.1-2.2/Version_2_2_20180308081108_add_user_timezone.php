<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Akeneo\UserManagement\Bundle\Entity\User;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_2_2_20180308081108_add_user_timezone extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oro_user ADD timezone VARCHAR(30) NOT NULL');
        $this->addSql(sprintf('UPDATE oro_user SET timezone="%s"', User::DEFAULT_TIMEZONE));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
