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
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_pk PRIMARY KEY (id);
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_attribute_id_fk FOREIGN KEY (attribute_id) REFERENCES pim_catalog_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE;
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_attribute_code_unique UNIQUE (attribute_id, code);

        CREATE TABLE pim_catalog_table_column_select_option (
            column_id varchar(64) not null,
            code varchar(100) not null,
            labels json not null default ('{}')
        );
        ALTER TABLE pim_catalog_table_column_select_option
            ADD CONSTRAINT pim_catalog_table_column_select_option_pk PRIMARY KEY (column_id, code);
        ALTER TABLE pim_catalog_table_column_select_option
            ADD CONSTRAINT pim_catalog_table_column_select_option_column_fk FOREIGN KEY (column_id) REFERENCES pim_catalog_table_column(id) ON DELETE CASCADE ON UPDATE CASCADE;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
