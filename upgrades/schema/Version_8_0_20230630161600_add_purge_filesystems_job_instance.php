<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230630161600_add_purge_filesystems_job_instance extends AbstractMigration
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
                'code' => 'purge_filesystems',
                'label' => 'Purge filesystems',
                'job_name' => 'purge_filesystems',
                'status' => 0,
                'connector' => 'internal',
                'raw_parameters' => \serialize([]),
                'type' => 'pim_reset',
            ],
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}