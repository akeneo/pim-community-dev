<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230223170000_add_mass_delete_attribute_groups_job_instance extends AbstractMigration
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
                'code' => 'delete_attribute_groups',
                'label' => 'Mass delete attribute groups',
                'job_name' => 'delete_attribute_groups',
                'status' => 0,
                'connector' => 'Akeneo Mass Edit Connector',
                'raw_parameters' => \serialize([]),
                'type' => 'attribute_group_mass_delete',
            ],
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
