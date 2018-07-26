<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations adding new job instances as well as permissions.
 */
class Version_2_3_20180619092921_batch_jobs extends AbstractMigration
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
            'apply_assets_mass_upload_into_asset_collection' => '(\'apply_assets_mass_upload_into_asset_collection\', \'Process mass uploaded assets and add to product\', \'apply_assets_mass_upload_into_asset_collection\', 0, \'Akeneo Product Asset Connector\', \'a:0:{}\', \'mass_upload\')',
            'csv_product_model_proposal_import' => '(\'csv_product_model_proposal_import\', \'Demo CSV product model draft import\', \'csv_product_model_proposal_import\', 0, \'Akeneo CSV Connector\', \'a:16:{s:8:\"filePath\";s:28:\"/tmp/product_model_draft.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:19:\"familyVariantColumn\";s:14:\"family_variant\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}\', \'import\')',
            'xlsx_product_model_proposal_import' => '(\'xlsx_product_model_proposal_import\', \'Demo XLSX product model draft import\', \'xlsx_product_model_proposal_import\', 0, \'Akeneo XLSX Connector\', \'a:13:{s:8:\"filePath\";s:29:\"/tmp/product_model_draft.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:19:\"familyVariantColumn\";s:14:\"family_variant\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}\', \'import\')'
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
            'SELECT * FROM akeneo_batch_job_instance WHERE code = \''.$jobCode.'\''
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
                return '\''.$jobCode.'\'';
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
