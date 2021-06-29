<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CountProductModelsWithRemovedAttributeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_only_counts_product_models_with_a_removed_attribute()
    {
        $count = $this->getCountProductModelsWithRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(2, $count);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCountProductModelsWithRemovedAttribute(): CountProductModelsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_product_models_with_removed_attribute');
    }
}
