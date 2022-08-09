<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20220729152738_update_shared_catalog_job_instance_parameter_path extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $jobInstances = $this->getJobInstances();

        foreach ($jobInstances as $jobInstance) {
            $rawParameters = unserialize($jobInstance['raw_parameters']);
            $migratedRawParameters = $this->migrateRawParameters($rawParameters);

            $this->updateJobInstance($jobInstance['id'], serialize($migratedRawParameters));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getJobInstances(): array
    {
        $connection = $this->container->get('database_connection');
        $sql = <<<SQL
SELECT id, raw_parameters
FROM akeneo_batch_job_instance
WHERE connector = 'Akeneo Shared Catalogs'
SQL;

        $stmt = $connection->executeQuery($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function migrateRawParameters(array $rawParameters): array
    {
        if (!array_key_exists('filePath', $rawParameters)) {
            return $rawParameters;
        }

        $rawParameters['storage']['type'] = 'none';
        $rawParameters['storage']['file_path'] = $rawParameters['filePath'];

        unset($rawParameters['filePath']);

        return $rawParameters;
    }

    private function updateJobInstance(string $jobInstanceId, string $serializedRawParameters)
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_instance
SET raw_parameters = :raw_parameters
WHERE id = :job_instance_id
SQL;

        $this->addSql($sql, ['job_instance_id' => $jobInstanceId, 'raw_parameters' => $serializedRawParameters]);
    }
}
