<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductUuidsWithRemovedAttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetProductUuidsWithRemovedAttributeIntegration extends TestCase
{
    private array $productIdentifierToUuidMapping;

    public function setUp(): void
    {
        parent::setUp();

        $fixtureLoader = $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute');
        $fixtureLoader->load();
        $this->productIdentifierToUuidMapping = $fixtureLoader->getproductIdentifierToUuidMapping();
    }

    public function test_it_retrieves_product_uuids_with_a_removed_attribute()
    {
        $result = $this->getProductUuidsWithRemovedAttribute()->nextBatch(['an_attribute', 'a_third_attribute'], 10);

        $expectedUuids = [
            $this->productIdentifierToUuidMapping['product_1'],
            $this->productIdentifierToUuidMapping['product_2'],
            $this->productIdentifierToUuidMapping['product_4'],
            $this->productIdentifierToUuidMapping['product_5'],
            $this->productIdentifierToUuidMapping['product_6'],
        ];
        \sort($expectedUuids);

        $batchCount = 0;
        foreach ($result as $batch) {
            $batchCount++;
            self::assertEquals($expectedUuids, $batch);
        }
        self::assertEquals(1, $batchCount);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getProductUuidsWithRemovedAttribute(): GetProductUuidsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_uuids_with_removed_attribute');
    }
}
