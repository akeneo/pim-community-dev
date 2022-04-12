<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;

final class UpdateProductsWhenElasticsearchIsDesynchronisedIntegration extends AbstractProductQueryBuilderTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     * See https://akeneo.atlassian.net/browse/PIM-10232
     * In this test we simulate an Elasticsearch desynchro and a job that updates products per batch of 100.
     * If the "Doctrine\ORM\ORMInvalidArgumentException: A new entity was found through the relationship..." error
     * does not occur, the test is green.
     */
    public function it_can_enable_all_products(): void
    {
        // Remove a product in DB to simulate a desynchro between ES and MySQL
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_0');
        self::assertNotNull($product);
        $this->get('database_connection')->executeQuery(
            'DELETE FROM pim_catalog_product WHERE uuid = :product_uuid',
            ['product_uuid' => $product->getUuid()->getBytes()]
        );

        $batchSize = $this->getParameter('pim_job_product_batch_size');
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('enabled', Operators::EQUALS, false);
        $products = $pqb->execute();

        $batchedProducts = [];
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $product->setEnabled(true);
            $batchedProducts[] = $product;

            if ($batchSize <= \count($batchedProducts)) {
                $this->get('pim_catalog.saver.product')->saveAll($batchedProducts);
                $batchedProducts = [];
                $this->get('pim_connector.doctrine.cache_clearer')->clear();
            }
        }
        if (0 > \count($batchedProducts)) {
            $this->get('pim_catalog.saver.product')->saveAll($batchedProducts);
            $this->get('pim_connector.doctrine.cache_clearer')->clear();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $products = [];
        for ($i = 0; $i < 210; $i++) {
            $product = $this->get('pim_catalog.builder.product')->createProduct(\sprintf('product_%d', $i), null);
            $this->get('pim_catalog.updater.product')->update($product, [
                'enabled' => false,
                'values' => [],
            ]);
            $products[] = $product;
        }
        $this->get('pim_catalog.saver.product')->saveAll($products);

        $this->esProductClient->refreshIndex();
    }
}
