<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20220726143600_add_scheduled_jobs extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $this->addScheduledJob('versioning_refresh', 'Refresh versioning for any updated entities', []);
    }

    private function addScheduledJob(string $jobCode, string $label, array $rawParameters): void
    {
        if (!$this->jobInstanceExists($jobCode)) {
            $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance 
                (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
            (
                :code,
                :label,
                :code,
                0,
                'internal',
                :raw_parameters,
                'scheduled_job'
            );
        SQL;

            $this->addSql(
                $sql,
                ['code' => $jobCode, 'label' => $label, 'raw_parameters' => \serialize($rawParameters)]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobInstanceExists(string $jobCode): bool
    {
        $sql = "SELECT id FROM akeneo_batch_job_instance WHERE code = :jobCode";

        $jobId = $this->dbalConnection()->executeQuery($sql, ['jobCode' => $jobCode])
                ->fetchFirstColumn()[0] ?? null;

        return $jobId !== null;
    }

    private function dbalConnection(): Connection
    {
        return $this->container->get('database_connection');
    }
}
