<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Updates the job instance table to add a new parameter for the asset export jobs (asset_manager_csv_asset_export)
 * The raw_parameters is updated to add a with_prefix_suffix to true if it does not exist.
 */
final class Version_5_0_20201208132458_update_asset_export_prefix_suffix extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $jobInstances = $this->getJobInstances();
        $updatedJobInstances = $this->addFormerValue($jobInstances);
        $this->save($updatedJobInstances);
    }

    private function getJobInstances(): array
    {
        $connection = $this->container->get('database_connection');
        $stmt = $connection->executeQuery(<<<SQL
SELECT id, raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name IN (:job_names)
SQL,
            [
                'job_names' => [
                    'asset_manager_csv_asset_export',
                    'asset_manager_xlsx_asset_export'
                ]
            ], [
                'job_names' => Connection::PARAM_STR_ARRAY
            ]
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function addFormerValue(array $jobInstances): array
    {
        $result = [];
        foreach ($jobInstances as $jobInstance) {
            $rawParameters = unserialize($jobInstance['raw_parameters']);
            if (!isset($rawParameters['with_prefix_suffix'])) {
                $rawParameters['with_prefix_suffix'] = true;
            }

            $result[] = [
                'id' => $jobInstance['id'],
                'raw_parameters' => serialize($rawParameters),
            ];
        }

        return $result;
    }

    private function save(array $updatedJobInstances)
    {
        foreach ($updatedJobInstances as $jobInstance) {
            $sql = <<<SQL
UPDATE akeneo_batch_job_instance
SET raw_parameters = :raw_parameters
WHERE id = :id
SQL;

            $this->addSql($sql, $jobInstance);
        }
    }
}
