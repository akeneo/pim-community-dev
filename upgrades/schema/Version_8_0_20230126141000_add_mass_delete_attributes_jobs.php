<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230126141000_add_mass_delete_attributes_jobs extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance 
                (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
            (
                :code,
                :label,
                :job_name,
                :status,
                :connector,
                :raw_parameters,
                :type
            )
            ON DUPLICATE KEY UPDATE code = code;
        SQL;

        $this->addSql(
            $sql,
            [
                'code' => 'delete_attributes',
                'label' => 'Mass delete attributes',
                'job_name' => 'delete_attributes',
                'status' => 0,
                'connector' => 'Akeneo Mass Edit Connector',
                'raw_parameters' => \serialize([]),
                'type' => 'attribute_mass_delete',
            ],
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
