<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Test\Integration\TestCase;

class DescendantProductModelIdsIntegration extends TestCase
{
    public function test_it_fetches_product_ids_from_product_model_ids()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('amor');
        $modelIds = array_map(function ($productModel) {
            return (int) $productModel->getId();
        }, $productModel->getProductModels()->toArray());

        $query = $this->get('pim_catalog.query.descendant_product_model_ids');

        $resultRows = $query->fetchFromParentProductModelId($productModel->getId());
        $this->assertCount(count($modelIds), $resultRows);
        $this->assertSame($modelIds, $resultRows);

        $this->assertNull($this->get('pim_catalog.repository.product_model')->find(81));
        $resultRows = $query->fetchFromParentProductModelId(81);
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
