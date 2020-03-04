<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

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
final class Version_4_0_20200117145507_remove_compute_model_descendant_jobs extends AbstractMigration implements
    ContainerAwareInterface
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
        $productModelCodes = $this->getBatchProductModelCodesForJobId();
        foreach (array_chunk($productModelCodes, 1000) as $batchProductModelCodes) {
            $this->computeAndIndexFromProductModelCodes($batchProductModelCodes);
        }

        $this->removeComputeModelDescendantJobs();
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getBatchProductModelCodesForJobId(): array
    {
        $sql = <<<SQL
            SELECT DISTINCT
                product_model_in_job.code 
            FROM 
                akeneo_batch_job_instance job_instance  
                INNER JOIN akeneo_batch_job_execution job_execution ON job_instance.id = job_execution.job_instance_id
                INNER JOIN akeneo_batch_job_execution_queue queue ON queue.job_execution_id = job_execution.id,
                JSON_TABLE(job_execution.raw_parameters, '$.product_model_codes[*]' COLUMNS (
                    code text PATH '$'
                )) product_model_in_job
            WHERE 
                job_instance.code = 'compute_product_models_descendants'
                AND queue.consumer IS NULL
SQL;

        return $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function removeComputeModelDescendantJobs()
    {
        $sql = <<<SQL
            DELETE queue
            FROM 
                akeneo_batch_job_instance job_instance
                INNER JOIN akeneo_batch_job_execution job_execution ON job_execution.job_instance_id = job_instance.id
                INNER JOIN akeneo_batch_job_execution_queue queue ON job_execution.id = queue.job_execution_id
            WHERE job_instance.code = 'compute_product_models_descendants'
        SQL;

        $this->addSql($sql);

        $sql = <<<SQL
            DELETE job_execution
            FROM 
                akeneo_batch_job_instance job_instance
                INNER JOIN akeneo_batch_job_execution job_execution ON job_execution.job_instance_id = job_instance.id
            WHERE job_instance.code = 'compute_product_models_descendants'
        SQL;

        $this->addSql($sql);

        $sql = <<<SQL
            DELETE job_instance
            FROM 
                akeneo_batch_job_instance job_instance
            WHERE job_instance.code = 'compute_product_models_descendants'
        SQL;

        $this->addSql($sql);
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
