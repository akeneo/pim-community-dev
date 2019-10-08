<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * As the job "compute product models descendants" is removed in v4, this migration checks the jobs in the queue and:
 *  - load product model codes involved in "compute product models descendants" future jobs (= in the queue)
 *  - recompute completeness and index the trees for those product model codes
 *  - remove the  "compute product models descendants" future jobs in DB
 */
final class Version_4_0_20191004145507_remove_compute_model_descendant_jobs
    extends AbstractMigration
    implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $jobs = $this->getComputeModelDescendantJobsInQueue();
        if (empty($jobs)) {
            return;
        }

        $productModelCodes = [];
        foreach ($jobs as $job) {
            $rawParameters = \json_decode($job['raw_parameters'], true);
            $productModelCodes = array_merge($productModelCodes, $rawParameters['product_model_codes']);
        }

        if (empty($productModelCodes)) {
            return;
        }

        $this->computeAndIndexFromProductModelCodes(array_unique($productModelCodes));
        $this->removeComputeModelDescendantJobs(array_column($jobs, 'job_execution_id'));
    }

    public function down(Schema $schema) : void
    {
    }

    private function getComputeModelDescendantJobsInQueue(): array
    {
        $sql = <<<SQL
SELECT
    execution.id AS job_execution_id,
    execution.raw_parameters,
    queue.id AS job_execution_queue_id
FROM akeneo_batch_job_execution execution
    INNER JOIN akeneo_batch_job_execution_queue queue ON execution.id = queue.job_execution_id
    INNER JOIN akeneo_batch_job_instance instance ON execution.job_instance_id = instance.id
WHERE execution.end_time IS NULL AND instance.code = 'compute_product_models_descendants'
SQL;

        return $this->connection->executeQuery($sql)->fetchAll();
    }

    private function removeComputeModelDescendantJobs(array $jobIds)
    {
        $sql = 'DELETE FROM akeneo_batch_job_execution_queue WHERE job_execution_id IN (:job_ids)';
        $this->addSql($sql, ['job_ids' => $jobIds], ['job_ids' => Connection::PARAM_INT_ARRAY]);

        $sql = 'DELETE FROM akeneo_batch_job_execution WHERE id IN (:job_ids)';
        $this->addSql($sql, ['job_ids' => $jobIds], ['job_ids' => Connection::PARAM_INT_ARRAY]);

        $this->addSql("DELETE FROM akeneo_batch_job_instance WHERE code = 'compute_product_models_descendants'");
    }

    private function computeAndIndexFromProductModelCodes(array $productModelCodes): void
    {
        $variantProductIdentifiers = $this
            ->container
            ->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_identifiers')
            ->fromProductModelCodes($productModelCodes);
        if (!empty($variantProductIdentifiers)) {
            $this
                ->container
                ->get('pim_catalog.completeness.product.compute_and_persist')
                ->fromProductIdentifiers($variantProductIdentifiers);
        }

        $this
            ->container
            ->get('pim_catalog.elasticsearch.indexer.product_model_descendants_and_ancestors')
            ->indexFromProductModelCodes($productModelCodes);
    }
}
