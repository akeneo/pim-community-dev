<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221019174400_add_template_uuid_column_on_category extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_category')->hasColumn('category_template_uuid'),
            'category_template_uuid column already exists in pim_catalog_category'
        );

        $this->addSql(<<<SQL
            ALTER TABLE pim_catalog_category ADD category_template_uuid binary(16) NULL;
            ALTER TABLE pim_catalog_category ADD CONSTRAINT FK_CATEGORY_template_uuid FOREIGN KEY (category_template_uuid) REFERENCES pim_catalog_category_template(uuid);
            SQL
        );


    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
