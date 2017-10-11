<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return VariantProductInterface
     * @throws \Exception
     */
    protected function createVariantProduct($identifier, array $data = []) : VariantProductInterface
    {
        $product = $this->get('pim_catalog.builder.variant_product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    protected function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->waitForProductModelsDescendantsJob();

        $this->get('akeneo_elasticsearch.client.product_model')->refreshIndex();

        return $productModel;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            \PHPUnit_Framework_Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * Each time we create a product model, a batch job is ran to calculate the
     * completeness of its descendants.
     *
     * This is done by a batch job, and if several product models are created one
     * after the other, we can end up with a MySQL error because several jobs run
     * at the same time.
     *
     * So this method ensures the completeness is calculated, but will stop and
     * throw an exception after 2 seconds max (we loops as we loop every 0.2
     * seconds).
     *
     */
    private function waitForProductModelsDescendantsJob()
    {
        $loop = 0;
        $count = 0;
        while (10 > $loop && 0 !== $count = $this->getComputeProductModelsDescendantsJobExecutionCount()) {
            usleep(200000);
            $loop++;
        }

        if (0 !== $count) {
            throw new \PHPUnit_Framework_IncompleteTestError(sprintf('There is still running "" jobs: %d', $count));
        }
    }

    /**
     * Finds the number of execution for a project calculation job.
     *
     * @return int
     */
    private function getComputeProductModelsDescendantsJobExecutionCount()
    {
        $sql = <<<SQL
SELECT count(`execution`.`id`)
FROM `akeneo_batch_job_execution` AS `execution`
LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
WHERE `instance`.`code` = 'compute_product_models_descendants'
AND `execution`.`exit_code` != 'COMPLETED'
SQL;

        return (int) $this->get('doctrine.orm.default_entity_manager')->getConnection()->fetchColumn($sql);
    }
}
