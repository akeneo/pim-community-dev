<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_2_0_171220144835_batch_jobs extends AbstractMigration
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
            'add_association'                          => '(\'add_association\', \'Mass associate products\', \'add_association\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
            'move_to_category'                         => '(\'move_to_category\', \'Mass move to categories\', \'move_to_category\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
            'add_to_category'                          => '(\'add_to_category\', \'Mass add to categories\', \'add_to_category\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
            'remove_from_category'                     => '(\'remove_from_category\', \'Mass remove from categories\', \'remove_from_category\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
            'add_to_existing_product_model'            => '(\'add_to_existing_product_model\', \'Add to existing product model\', \'add_to_existing_product_model\', 0, \'Akeneo Mass Edit Connector\', \'a:0:{}\', \'mass_edit\')',
            'compute_family_variant_structure_changes' => '(\'compute_family_variant_structure_changes\', \'Compute variant structure changes\', \'compute_family_variant_structure_changes\', 0, \'internal\', \'a:0:{}\', \'compute_family_variant_structure_changes\')',
            'compute_completeness_of_products_family'  => '(\'compute_completeness_of_products_family\', \'compute completeness of products family\', \'compute_completeness_of_products_family\', 0, \'internal\', \'a:0:{}\', \'compute_completeness_of_products_family\')',
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
