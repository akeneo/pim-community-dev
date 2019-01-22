<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds new compute_completeness_of_products_linked_to_assets job
 */
class Version_2_3_20181127102702_add_batch_job extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $newJobCodes = $this->getNewJobs();
        $jobInstancesToCreate = $this->jobInstancesToCreate($newJobCodes);
        $this->insertJobs($jobInstancesToCreate);
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
            'compute_completeness_of_products_linked_to_assets' => '(\'compute_completeness_of_products_linked_to_assets\', \'Compute completeness of products linked to assets\', \'compute_completeness_of_products_linked_to_assets\', 0, \'internal\', \'a:0:{}\', \'compute_completeness_of_products_linked_to_assets\')',
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
                $jobInstancesToCreate[$jobCode] = $jobInstance;
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
    private function insertJobs(array $jobInstancesToCreate): void
    {
        if (empty($jobInstancesToCreate)) {
            return;
        }

        $jobInstancesSQL = $this->getJobInstanceRow($jobInstancesToCreate);
        $this->insertJobInstances($jobInstancesSQL);
        $jobInstancesPermissionsSQL = $this->getJobInstancesCodes($jobInstancesToCreate);
        $this->insertJobInstancePermissions($jobInstancesPermissionsSQL);
    }

    /**
     * @param $jobInstancesSQL
     *
     */
    private function insertJobInstances(string $jobInstancesSQL): void
    {
        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
$jobInstancesSQL
;
SQL;
        $this->addSql($sql);
    }

    /**
     * @param array $jobInstancesToCreate
     *
     * @return string
     */
    private function getJobInstanceRow(array $jobInstancesToCreate): string
    {
        return implode(',', array_values($jobInstancesToCreate));
    }

    /**
     * @param array $jobInstancesToCreate
     *
     * @return string
     */
    private function getJobInstancesCodes(array $jobInstancesToCreate): string
    {
        $jobInstancesToCreate = array_map(
            function (string $jobCode) {
                return '\'' . $jobCode . '\'';
            },
            array_keys($jobInstancesToCreate)
        );

        return implode(',', $jobInstancesToCreate);
    }

    /**
     * @param string $jobInstancesPermissionsSQL
     */
    private function insertJobInstancePermissions(string $jobInstancesPermissionsSQL): void
    {
        $this->addSql(
            <<<SQL
            INSERT INTO pimee_security_job_profile_access
                (`job_profile_id`, `user_group_id`, `execute_job_profile`, `edit_job_profile`)
                    SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
                    FROM akeneo_batch_job_instance as j
                    JOIN oro_access_group AS g ON g.name = "All"
                    WHERE j.code IN (
                        $jobInstancesPermissionsSQL
                    )
            ;
SQL
        );
    }
}
