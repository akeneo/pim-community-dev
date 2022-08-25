<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220823150400_add_supplier_portal_supplier_product_files_clean_job extends AbstractMigration
{
    private const JOB_CODE = 'supplier_portal_supplier_product_files_clean';

    public function up(Schema $schema): void
    {
        if (!$this->jobInstanceExists(self::JOB_CODE)) {
            $sql = <<<SQL
                INSERT INTO akeneo_batch_job_instance 
                    (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
                VALUES
                (
                    :code,
                    :label,
                    :code,
                    0,
                    'Supplier Portal',
                    :raw_parameters,
                    'scheduled_job'
                );
            SQL;

            $this->addSql(
                $sql,
                [
                    'code' => self::JOB_CODE,
                    'label' => 'Clean old supplier product files',
                    'raw_parameters' => \serialize([])
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
