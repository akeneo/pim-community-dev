<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230127150758_add_ondelete_cascade_on_category_template_attributes extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE pim_catalog_category_attribute
            drop CONSTRAINT FK_ATTRIBUTE_template_uiid;
            ALTER TABLE pim_catalog_category_attribute
            ADD CONSTRAINT FK_ATTRIBUTE_template_uuid
                FOREIGN KEY (`category_template_uuid`) 
                REFERENCES `pim_catalog_category_template` (`uuid`)
                ON DELETE CASCADE;
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
