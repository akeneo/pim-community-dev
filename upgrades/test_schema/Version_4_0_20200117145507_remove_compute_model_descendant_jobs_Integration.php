<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * This class will be removed after 4.0 version
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_4_0_20200117145507_remove_compute_model_descendant_jobs_Integration extends AbstractPimCatalogTestCase
{
    use ExecuteMigrationTrait;

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_computes_products_and_remove_jobs()
    {
        $this->createJobs();
        $this->createProductsAndProductModels();

        $ESQuery = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'identifier' => ['product1', 'product2', 'product3'],
                        ],
                    ],
                ],
            ],
        ];

        Assert::assertNotNull($this->getComputeProductModelDescendantJobId());
        $productsFound = $this->getSearchQueryResults($ESQuery);
        Assert::assertCount(0, $productsFound);

        $this->reExecuteMigration($this->getMigrationLabel());
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        Assert::assertNull($this->getComputeProductModelDescendantJobId());
        Assert::assertSame(0, $this->numberOfJobExecutions());
        Assert::assertSame(0, $this->numberOfJobsInQueue());

        $productsFound = $this->getSearchQueryResults($ESQuery);
        Assert::assertCount(3, $productsFound);
    }

    protected function createJobs()
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES ('compute_product_models_descendants', 'Compute product models descendants', 'compute_product_models_descendants', 0, 'internal', 'a:0:{}', 'compute_product_models_descendants')
SQL;
        $this->getConnection()->exec($sql);

        $jobId = $this->getComputeProductModelDescendantJobId();
        Assert::assertNotNull($jobId);

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_execution (id, job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES
(1000, $jobId, null, 'admin', 2, null, null, '2019-10-07 07:50:24', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{"product_model_codes": ["pm1"]}'),
(1001, $jobId, null, 'admin', 2, null, null, '2019-10-07 07:51:36', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{"product_model_codes": ["pm1", "pm2"]}')
;
SQL;
        $this->getConnection()->exec($sql);

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_execution_queue (id, job_execution_id, options, consumer, create_time, updated_time)
VALUES
(2000, 1000, '{"env": "prod"}', null, '2019-10-07 07:50:24', null),
(2001, 1001, '{"env": "prod"}', null, '2019-10-07 07:51:36', null)
SQL;
        $this->getConnection()->exec($sql);
    }

    protected function createProductsAndProductModels(): void
    {
        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('familyVariantA1');
        $familyVariantId = $familyVariant->getId();

        $sql = <<<SQL
INSERT INTO pim_catalog_product_model (id, code, family_variant_id, raw_values, created, updated, root, lvl, lft, rgt)
VALUES
    (3000, 'pm1', $familyVariantId, '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW(), 0, 0, 0, 0),
    (3001, 'pm2', $familyVariantId, '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW(), 0, 0, 0, 0)
SQL;
        $this->getConnection()->exec($sql);

        $sql = <<<SQL
INSERT INTO pim_catalog_product (id, product_model_id, is_enabled, identifier, raw_values, created, updated)
VALUES
    (5000, 3000, 1, 'product1', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW()),
    (5001, 3000, 1, 'product2', '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW()),
    (5002, 3001, 1, 'product3', '{"name": {"<all_channels>": {"<all_locales>": [""]}}}', NOW(), NOW())
SQL;
        $this->getConnection()->exec($sql);
    }

    protected function getComputeProductModelDescendantJobId(): ?int
    {
        $sql = "SELECT id FROM akeneo_batch_job_instance WHERE code = 'compute_product_models_descendants'";

        $jobId = $this->getConnection()->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN, 0)[0] ?? null;

        return $jobId === null ? null : (int) $jobId;
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
    }

    private function numberOfJobExecutions(): int
    {
        return (int) $this->getConnection()->executeQuery('SELECT COUNT(*) AS count FROM akeneo_batch_job_execution')->fetch()['count'];
    }

    private function numberOfJobsInQueue(): int
    {
        return (int) $this->getConnection()->executeQuery('SELECT COUNT(*) AS count FROM akeneo_batch_job_execution_queue')->fetch()['count'];
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
