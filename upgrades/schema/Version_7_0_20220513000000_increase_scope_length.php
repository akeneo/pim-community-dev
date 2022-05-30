<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220513000000_increase_scope_length extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE pim_api_access_token MODIFY scope VARCHAR(1000) DEFAULT NULL");
        $this->addSql("ALTER TABLE pim_api_refresh_token MODIFY scope VARCHAR(1000) DEFAULT NULL");
        $this->addSql("ALTER TABLE pim_api_auth_code MODIFY scope VARCHAR(1000) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
