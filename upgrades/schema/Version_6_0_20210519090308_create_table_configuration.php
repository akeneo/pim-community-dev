<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create table pim_catalog_table_column
 */
final class Version_6_0_20210519090308_create_table_configuration extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
        create table pim_catalog_table_column (
            id varchar(137) not null,
            attribute_id int not null,
            code varchar(100) not null,
            data_type varchar(20) not null,
            column_order int not null,
            validations json not null default ('{}'),
            labels json not null default ('{}')
        );
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_pk primary key (id);
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_id_fk foreign key (attribute_id) REFERENCES pim_catalog_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE;
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_code_unique unique (attribute_id, code);
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_order_unique unique (attribute_id, column_order);
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
