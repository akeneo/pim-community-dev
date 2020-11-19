<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetProductModelIdentifiersWithRemovedAttributeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_retrieves_product_identifiers_with_a_removed_attribute()
    {
        $result = $this->getProductModelIdentifiersWithRemovedAttribute()->nextBatch(['an_attribute', 'a_third_attribute'], 10);

        $batchCount = 0;
        foreach ($result as $batch) {
            $batchCount++;
            self::assertEquals(['a_product_model', 'a_sub_product_model'], $batch);
        }
        self::assertEquals(1, $batchCount);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getProductModelIdentifiersWithRemovedAttribute(): GetProductModelIdentifiersWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_model_identifiers_with_removed_attribute');
    }
}
