<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220704154900_supplier_portal_rename_tables extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("RENAME TABLE akeneo_onboarder_serenity_contributor_account TO akeneo_supplier_portal_contributor_account");
        $this->addSql("RENAME TABLE akeneo_onboarder_serenity_supplier_contributor TO akeneo_supplier_portal_supplier_contributor");
        $this->addSql("RENAME TABLE akeneo_onboarder_serenity_supplier TO akeneo_supplier_portal_supplier");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
