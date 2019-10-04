<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Test\Integration\TestCase;

class DescendantProductIdsIntegration extends TestCase
{
    public function test_it_fetches_product_ids_from_product_model_ids()
    {
        $query = $this->get('pim_catalog.query.descendant_product_ids');

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('amor');
        $productModel2 = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('aphrodite');

        $expectedProductIds = array_map(function ($product) {
            return (int) $product->getId();
        }, $productModel->getProducts()->toArray());

        $resultRows = $query->fetchFromProductModelIds([$productModel->getId()]);
        $this->assertCount(count($expectedProductIds), $resultRows);
        $this->assertSame($expectedProductIds, $resultRows);

        $products = array_merge($productModel->getProducts()->toArray(), $productModel2->getProducts()->toArray());
        $expectedProductIds = array_map(function ($product) {
            return (int) $product->getId();
        }, $products);

        $resultRows = $query->fetchFromProductModelIds([$productModel->getId(), $productModel2->getId()]);
        $this->assertCount(count($expectedProductIds), $resultRows);
        $this->assertSame($expectedProductIds, $resultRows);

        $this->assertNull($this->get('pim_catalog.repository.product_model')->find(81));
        $resultRows = $query->fetchFromProductModelIds([81]);
        $this->assertCount(0, $resultRows);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
