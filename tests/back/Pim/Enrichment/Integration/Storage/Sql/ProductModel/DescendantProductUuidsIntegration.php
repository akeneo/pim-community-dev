<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\UuidInterface;

class DescendantProductUuidsIntegration extends TestCase
{
    public function test_it_fetches_product_uuids_from_product_model_ids()
    {
        $query = $this->get('pim_catalog.query.descendant_product_ids');

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('amor');
        $productModel2 = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('aphrodite');

        $expectedProductUuids = array_map(
            fn ($product): UuidInterface => $product->getUuid(),
            $productModel->getProducts()->toArray()
        );

        $resultRows = $query->fetchFromProductModelIds([$productModel->getId()]);
        $this->assertCount(count($expectedProductUuids), $resultRows);
        $this->assertEquals($expectedProductUuids, $resultRows);

        $products = array_merge($productModel->getProducts()->toArray(), $productModel2->getProducts()->toArray());
        $expectedProductUuids = array_map(
            fn ($product): UuidInterface => $product->getUuid(),
            $products
        );

        $resultRows = $query->fetchFromProductModelIds([$productModel->getId(), $productModel2->getId()]);
        $this->assertCount(count($expectedProductUuids), $resultRows);
        $this->assertEquals($expectedProductUuids, $resultRows);

        $resultRows = $query->fetchFromProductModelIds([0]);
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
