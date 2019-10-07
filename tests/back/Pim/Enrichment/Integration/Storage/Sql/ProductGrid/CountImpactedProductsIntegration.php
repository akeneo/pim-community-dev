<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountImpactedProductsIntegration extends TestCase
{
    public function testUserSelectedMultipleProducts()
    {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_1', 'product_2', 'product_3'],
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 3);
    }

    public function testUserSelectedOneProductModel()
    {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_3'],
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 13);
    }

    public function testUserSelectedMultipleProductModels()
    {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_3', 'product_model_2'],
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 18);
    }

    public function testUserSelectedMultipleProductModelsAndProducts()
    {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_3', 'product_model_2', 'product_3', 'product_4'],
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 20);
    }

    public function testUserSelectedAllEntities()
    {
        $pqbFilters = [];
        $this->assertProductsCountInSelection($pqbFilters, 242);
    }

    public function testUserSelectedAllEntitiesWithEmptyAttributeFilter()
    {
        // top_composition only belongs of the 'Shoes' family, so the query should only return shoes products
        $pqbFilters = [
            [
                'field' => 'top_composition',
                'operator' => 'EMPTY',
                'value' => null,
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->assertProductsCountInSelection($pqbFilters, 65);
    }

    public function testUserSelectedAllEntitiesAndFilteredByLabel()
    {
        $pqbFilters = [
            ['field' => 'label_or_identifier', 'operator' => 'CONTAINS', 'value' => 'di', 'type' => 'field']
        ];
        $this->assertProductsCountInSelection($pqbFilters, 29);
    }

    public function testUserSelectedAllEntitiesAndUnselectsProducts()
    {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_1', 'product_2', 'product_3', 'product_4'],
                'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce', 'limit' => 25, 'from' => 0],
                'type'     => 'field',
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 238);
    }

    public function testUserSelectedAllEntitiesAndUnselectsProductModels()
    {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_model_1', 'product_model_2'],
                'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce', 'limit' => 25, 'from' => 0],
                'type'     => 'field',
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 234);
    }

    public function testUserSelectedAllEntitiesAndUnselectsProductsAndProductModels()
    {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_1', 'product_model_2'],
                'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce', 'limit' => 25, 'from' => 0],
                'type'     => 'field',
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 236);
    }

    public function testUserSelectedAllVisibleEntities()
    {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => [
                    'product_1',
                    'product_2',
                    'product_3',
                    'product_4',
                    'product_5',
                    'product_model_1',
                    'product_model_2',
                    'product_model_3',
                    'product_model_4',
                    'product_model_5',
                    'product_model_6',
                    'product_model_7',
                    'product_model_8',
                    'product_model_9',
                    'product_model_10',
                    'product_model_11',
                    'product_model_12',
                    'product_model_13',
                    'product_model_14',
                    'product_model_15',
                    'product_model_16',
                    'product_model_17',
                    'product_model_18',
                    'product_model_19',
                    'product_model_20',
                ],
                'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 117);
    }

    public function testUserSelectedAllEntitiesWithAdditionnalFilter()
    {
        $pqbFilters = [
            [
                'field' => 'color',
                'operator' => 'IN',
                'value' => ['crimson_red'],
                'context' => [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'limit'  => 25,
                    'from'   => 0,
                    'field'  => 'color',
                ],
                'type' => 'attribute',
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 12);
    }

    /**
     * @link jira https://akeneo.atlassian.net/browse/PIM-7442
     */
    public function testUserSelectedAllEntitiesWithAdditionnalFilterOnParent()
    {
        $pqbFilters = [
            [
                'field'    => 'parent',
                'operator' => 'IN',
                'value'    => ['venus'],
                'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce', 'limit' => 25, 'from' => 0],
                'type'     => 'field',
            ],
        ];
        $this->assertProductsCountInSelection($pqbFilters, 3);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param array $pqbFilters
     * @param int   $expectedProductsCount
     */
    private function assertProductsCountInSelection(array $pqbFilters, int $expectedProductsCount): void
    {
        $productsCount = $this->get('pim_enrich.doctrine.query.count_impacted_products')
            ->count($pqbFilters);
        $this->assertEquals($expectedProductsCount, $productsCount);
    }
}
