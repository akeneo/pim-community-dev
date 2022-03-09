<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Job;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

final class Version_6_0_20210531152335_dqi_launch_recompute_products_scores extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $jobStatus = Job\BatchStatus::STARTING;

        // Initialize job execution
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_execution (job_instance_id, user, status, raw_parameters)
            VALUES (
                (SELECT id FROM akeneo_batch_job_instance WHERE code = 'data_quality_insights_recompute_products_scores'),
                'system',${jobStatus},'{"lastProductId": 0}'
            );
        SQL);
    }

    public function postUp(Schema $schema): void
    {
        // Retrieve the job execution id
        $statement = $this->container->get('database_connection')->executeQuery(<<<SQL
            SELECT abje.id AS job_execution_id FROM akeneo_batch_job_execution abje
            INNER JOIN akeneo_batch_job_instance abji ON abje.job_instance_id = abji.id
            WHERE abji.code = 'data_quality_insights_recompute_products_scores';
        SQL);

        $jobExecutionId = $statement->fetchOne();

        $this->abortIf($jobExecutionId === false, 'The `data_quality_insights_recompute_products_scores` job execution is not initialized');

        // Add the job execution in the maintenance job queue
        $jobExecutionMessage = DataMaintenanceJobExecutionMessage::createJobExecutionMessageFromNormalized([
            'id' => Uuid::uuid4(),
            'job_execution_id' => (int) $jobExecutionId,
        ]);

        $this->container->get('akeneo_batch_queue.queue.job_execution_queue')->publish($jobExecutionMessage);
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        Assert::notNull($container);
        $this->container = $container;
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
