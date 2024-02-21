<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetExistingProductModelCodes;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelExistingAmongIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_gets_existing_product_models(): void
    {
        $productModelCodes = $this->getProductModelExistingAmong()->among(['a_product_model', 'a_sub_product_model', 'unknown_model']);
        $result = [...$productModelCodes];

        $this->assertEquals(['a_product_model', 'a_sub_product_model'], $result);
    }

    private function getProductModelExistingAmong(): GetExistingProductModelCodes
    {
        return $this->get(GetExistingProductModelCodes::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
