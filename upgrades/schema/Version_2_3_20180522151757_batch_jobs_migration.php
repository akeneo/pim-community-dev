<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_2_3_20180522151757_batch_jobs_migration extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $newJobCodes = $this->getNewJobs();
        $jobInstancesToCreate = $this->jobInstancesToCreate($newJobCodes);
        $this->insertJobInstances($jobInstancesToCreate);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * @return array
     *
     */
    private function getNewJobs(): array
    {
        return [
            'change_parent_product' => '(\'change_parent_product\', \'Change parent product model\', \'change_parent_product\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
        ];
    }

    /**
     * @param array $newJobCodes
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function jobInstancesToCreate(array $newJobCodes): array
    {
        $jobInstancesToCreate = [];
        foreach ($newJobCodes as $jobCode => $jobInstance) {
            if (!$this->jobExists($jobCode)) {
                $jobInstancesToCreate[] = $jobInstance;
            }
        }

        return $jobInstancesToCreate;
    }

    /**
     * @param string $jobCode
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function jobExists(string $jobCode): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM akeneo_batch_job_instance WHERE code = \'' . $jobCode . '\''
        );

        return 1 <= $stmt->rowCount();
    }

    /**
     * @param array $jobInstancesToCreate
     */
    private function insertJobInstances(array $jobInstancesToCreate): void
    {
        if (empty($jobInstancesToCreate)) {
            return;
        }
        $jobInstancesSQL = implode(',', $jobInstancesToCreate);
        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
$jobInstancesSQL
;
SQL;
        $this->addSql($sql);
    }
}
