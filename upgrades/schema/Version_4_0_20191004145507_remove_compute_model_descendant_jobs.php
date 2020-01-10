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
final class Version_4_0_20191004145507_remove_compute_model_descendant_jobs extends AbstractMigration implements
    ContainerAwareInterface
{
    private const BATCH_SIZE = 10000;

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
        $jobInstanceId = $this->getComputeProductModelDescendantsJobId();
        if ($jobInstanceId === null) {
            return;
        }

        $batchProductModelCodes = $this->getBatchProductModelCodesForJobId($jobInstanceId);
        foreach ($batchProductModelCodes as $productModelCodes) {
            $this->computeAndIndexFromProductModelCodes($productModelCodes);
        }

        $this->removeComputeModelDescendantJobs($jobInstanceId);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getComputeProductModelDescendantsJobId(): ?int
    {
        $job = $this->container->get('pim_enrich.repository.job_instance')->findOneBy(
            ['code' => 'compute_product_models_descendants']
        );

        return $job === null ? null : $job->getId();
    }

    private function getBatchProductModelCodesForJobId(int $jobInstanceId): \Generator
    {
        $sql = <<<SQL
SELECT DISTINCT product_model.id, product_model.code
FROM
    akeneo_batch_job_execution,
    JSON_TABLE(raw_parameters, '$.product_model_codes[*]' COLUMNS (
        code text PATH '$'
    )) product_model_in_job
    INNER JOIN pim_catalog_product_model product_model ON BINARY product_model.code = BINARY product_model_in_job.code
WHERE job_instance_id = :jobInstanceId AND product_model.id > :formerId
ORDER BY product_model.id
LIMIT :limit
SQL;

        $formerId = -1;
        while (true) {
            $productModels = $this->connection->executeQuery(
                $sql,
                [
                    'jobInstanceId' => $jobInstanceId,
                    'formerId' => $formerId,
                    'limit' => self::BATCH_SIZE,
                ],
                [
                    'jobInstanceId' => \PDO::PARAM_INT,
                    'formerId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAll();

            if (empty($productModels)) {
                break;
            }

            $formerId = (int) end($productModels)['id'];
            yield array_column($productModels, 'code');
        }
    }

    private function removeComputeModelDescendantJobs(int $jobInstanceId)
    {
        $sql = <<<SQL
DELETE execution, queue
FROM  akeneo_batch_job_execution execution
    INNER JOIN akeneo_batch_job_execution_queue queue ON execution.id = queue.job_execution_id
WHERE execution.job_instance_id = :jobInstanceId
SQL;

        $this->addSql($sql, ['jobInstanceId' => $jobInstanceId], ['jobInstanceId' => \PDO::PARAM_INT]);

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
