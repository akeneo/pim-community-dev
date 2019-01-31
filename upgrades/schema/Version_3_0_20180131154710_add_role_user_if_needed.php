<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create if needed the ROLE_USER which is mandatory
 */
class Version_3_0_20180131154710_add_role_user_if_needed extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $roles = $this->connection->fetchArray("SELECT role FROM oro_access_role WHERE role = 'ROLE_USER'");

        if (false === $roles) {
            echo "The role ROLE_USER must exists in the PIM, we'll create it for you\n";
            $this->addSQL("INSERT INTO oro_access_role (role, label) VALUES ('ROLE_USER', 'User')");
        } else {
            echo "No need to create a new ROLE\n";
            $this->addSql('SELECT 1');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        throw new IrreversibleMigrationException();
    }
}
