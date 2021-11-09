<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_6_0_20211025161901_set_default_role_type extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE oro_access_role set type='default' WHERE type IS NULL");
        $this->addSql("ALTER TABLE oro_access_role MODIFY type VARCHAR(30) NOT NULL DEFAULT 'default'");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
