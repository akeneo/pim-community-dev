<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230817162126_add_purge_orphan_category_image_files_job_instance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the purge_orphan_category_image_files job instance';
    }

    public function up(Schema $schema): void
    {
        if (!$this->jobInstanceExists('purge_orphan_category_image_files')) {
            $this->addSql(
                <<<SQL
                INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
                VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type)
                ON DUPLICATE KEY UPDATE code = code;
                SQL,
                [
                    'code' => 'purge_orphan_category_image_files',
                    'label' => 'Purge orphan category image files',
                    'job_name' => 'purge_orphan_category_image_files',
                    'status' => 0,
                    'connector' => 'internal',
                    'raw_parameters' => \serialize([]),
                    'type' => 'purge_orphan_category_image_files',
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobInstanceExists(string $jobCode): bool
    {
        $sql = <<<SQL
            SELECT id
            FROM akeneo_batch_job_instance
            WHERE code = :jobCode
        SQL;

        return 0 !== $this->connection->executeQuery($sql, ['jobCode' => $jobCode])->rowCount();
    }
}
