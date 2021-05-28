<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\User;

final class Version20210527144217 extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->truncateProductsScoresTable();

        $this->createRecomputeJobInstance();

        $this->launchRecomputeJob();
    }

    private function truncateProductsScoresTable(): void
    {
        $sql = <<<SQL
TRUNCATE TABLE pim_data_quality_insights_product_score
SQL;
        $this->addSql($sql);
    }

    private function createRecomputeJobInstance(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
            (
                'data_quality_insights_recompute_products_scores',
                'data_quality_insights_recompute_products_scores',
                'data_quality_insights_recompute_products_scores',
                0,
                'Data Quality Insights Connector',
                'a:0:{}',
                'data_quality_insights'
            );
SQL;
        $this->addSql($sql);
    }

    private function launchRecomputeJob()
    {
        $jobInstance = $this->getJobInstance();
        $user = new User(UserInterface::SYSTEM_USER_NAME, null);
        $jobParameters = ['lastProductId' => 0];

        $this->container->get('akeneo_batch_queue.launcher.queue_job_launcher')->launch($jobInstance, $user, $jobParameters);
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->container->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('data_quality_insights_recompute_products_scores');

        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException('The job instance "data_quality_insights_recompute_products_scores" does not exist. Please contact your administrator.');
        }

        return $jobInstance;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
