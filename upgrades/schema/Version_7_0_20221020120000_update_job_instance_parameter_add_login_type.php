<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version_7_0_20221020120000_update_job_instance_parameter_add_login_type extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $jobInstancesToMigrate = $this->getJobInstancesToMigrate();

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
            WHERE type IN ('import', 'export')
        SQL;

        $stmt = $connection->executeQuery($sql);
        $jobInstances = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_filter($jobInstances, function (array $jobInstance) {
            $rawParameters = unserialize($jobInstance['raw_parameters']);

            return isset($rawParameters['storage']['type'])
                && 'sftp' === $rawParameters['storage']['type']
                && !isset($rawParameters['storage']['login_type']);
        });
    }

    private function migrateRawParameters(array $rawParameters): array
    {
        $rawParameters['storage']['login_type'] = 'password';

        return $rawParameters;
    }

    private function updateJobInstance(string $jobInstanceId, string $serializedRawParameters): void
    {
        $sql = <<<SQL
            UPDATE akeneo_batch_job_instance
            SET raw_parameters = :raw_parameters
            WHERE id = :job_instance_id
        SQL;

        $this->addSql($sql, ['job_instance_id' => $jobInstanceId, 'raw_parameters' => $serializedRawParameters]);
    }
}
