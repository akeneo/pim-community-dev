<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20220921104755_update_job_instance_parameter_user_to_notify_for_jobs extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $jobInstancesToMigrate = $this->getJobInstancesToMigrate();

        $this->skipIf(empty($jobInstancesToMigrate), 'No remaining job instance to migrate');

        foreach ($jobInstancesToMigrate as $jobInstance) {
            $rawParameters = unserialize($jobInstance['raw_parameters']);
            $migratedRawParameters = $this->migrateRawParameters($rawParameters);

            $this->updateJobInstance($jobInstance['id'], serialize($migratedRawParameters));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getJobInstancesToMigrate(): array
    {
        $connection = $this->container->get('database_connection');
        $sql = <<<SQL
SELECT id, raw_parameters
FROM akeneo_batch_job_instance
WHERE raw_parameters LIKE '%user_to_notify%'
SQL;

        $stmt = $connection->executeQuery($sql);
        $jobInstances = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_filter($jobInstances, function (array $jobInstance) {
            $rawParameters = unserialize($jobInstance['raw_parameters']);
            return array_key_exists('user_to_notify', $rawParameters);
        });
    }

    private function migrateRawParameters(array $rawParameters): array
    {
        $rawParameters['users_to_notify'] = null !== $rawParameters['user_to_notify'] ? [$rawParameters['user_to_notify']] : [];
        unset($rawParameters['user_to_notify']);

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
