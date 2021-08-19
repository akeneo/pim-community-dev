<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add current sources to concat element list to tailored export job profile, since the user can add separator between sources
 */
final class Version_6_0_20210818141014_add_source_concatenation extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $tailoredJobInstances = $this->getTailoredJobInstances();
        $this->skipIf(empty($tailoredJobInstances), 'No tailored job instance to migrate.');

        foreach ($tailoredJobInstances as $tailoredJobInstance) {
            $rawParameters = unserialize($tailoredJobInstance['raw_parameters']);
            $migratedRawParameters = $this->migrateRawParameters($rawParameters);

            $this->updateJobInstance($tailoredJobInstance['id'], serialize($migratedRawParameters));
        }
    }

    private function getTailoredJobInstances(): array
    {
        $connection = $this->container->get('database_connection');
        $sql = <<<SQL
SELECT id, raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name = 'xlsx_tailored_product_export'
SQL;

        $stmt = $connection->executeQuery($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function migrateRawParameters(array $rawParameters): array
    {
        $migratedRawParameters = $rawParameters;
        foreach ($rawParameters['columns'] as $index => $column) {
            if (!empty($column['format']['element'])) {
                continue;
            }

            $migratedRawParameters['columns'][$index]["format"]["space_between"] = true;
            $sourceUuids = array_column($column['sources'], 'uuid');
            $migratedRawParameters['columns'][$index]["format"]['elements'] = array_map(fn($sourceUuid) => [
                'uuid' => $sourceUuid,
                'type' => 'source',
                'value' => $sourceUuid,
            ], $sourceUuids);
        }

        return $migratedRawParameters;
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
