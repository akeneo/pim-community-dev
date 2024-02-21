<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetAllRootProductModelCodes;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllRootProductModelIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_gets_all_root_product_model(): void
    {
        $productModelCodes = $this->getAllRootProductModel()->byBatchesOf(10);
        $result = [...$productModelCodes];

        $this->assertEquals(['a_product_model', 'a_second_product_model'], $result[0]);
    }

    private function getAllRootProductModel(): GetAllRootProductModelCodes
    {
        return $this->get(GetAllRootProductModelCodes::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
