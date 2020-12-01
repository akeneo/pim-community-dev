<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201201092625_add_clean_removed_attribute_job extends AbstractMigration
{
    public function up(): void
    {
        if ($this->jobExists('clean_removed_attribute_job')) {
            return;
        }

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type);
SQL;
        $this->addSql($sql, [
            'code'           => 'clean_removed_attribute_job',
            'label'          => 'Clean the removed attribute values in product',
            'job_name'       => 'clean_removed_attribute_job',
            'status'         => 0,
            'connector'      => 'internal',
            'raw_parameters' => 'a:0:{}',
            'type'           => 'clean_removed_attribute_job',
        ]);
    }

    public function down(): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobExists(string $jobCode): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $jobCode]
        );

        return 1 <= $stmt->rowCount();
    }
}
