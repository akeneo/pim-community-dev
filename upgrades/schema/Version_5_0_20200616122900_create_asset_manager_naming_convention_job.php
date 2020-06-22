<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200616122900_create_asset_manager_naming_convention_job extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES
	        ('asset_manager_execute_naming_convention', 'Execution of Asset Family naming convention', 'asset_manager_execute_naming_convention', 0, 'internal', 'a:0:{}', 'asset_manager_execute_naming_convention')
        ;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
