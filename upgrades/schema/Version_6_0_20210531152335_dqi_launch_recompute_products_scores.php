<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Job;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Akeneo\Platform\Bundle\InstallerBundle\Migration\ContainerAwareMigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_6_0_20210531152335_dqi_launch_recompute_products_scores extends AbstractMigration implements ContainerAwareMigrationInterface
{
    private ?ContainerInterface $container = null;

    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

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

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
