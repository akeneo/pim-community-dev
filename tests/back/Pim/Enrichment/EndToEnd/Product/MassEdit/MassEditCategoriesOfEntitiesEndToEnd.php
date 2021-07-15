<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class MassEditCategoriesOfEntitiesEndToEnd extends AbstractMassEditEndToEnd
{
    public function test_adding_a_category_to_entities_produces_event(): void
    {
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111111', 'product'), // variant product
                        $this->findESIdFor('watch', 'product'), // product
                        $this->findESIdFor('apollon_yellow', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'add_to_category',
            'actions' => [
                [
                    'field' => 'categories',
                    'value' => ['master_men_pants_jeans'],
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'add_to_category',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);

        $product = $this->getProductWithInternalApi('watch');
        $this->assertCategories(
            ['supplier_zaro', 'master_men_pants_jeans'],
            $product['categories']
        );
        $variantProduct = $this->getProductWithInternalApi('1111111111');
        $this->assertCategories(
            ['master_men_blazers', 'supplier_zaro', 'master_men_pants_jeans'],
            $variantProduct['categories']
        );
        $productModel = $this->getProductModelWithInternalApi('apollon_yellow');
        $this->assertCategories(
            ['master_men_blazers_deals', 'supplier_zaro', 'master_men_pants_jeans'],
            $productModel['categories']
        );
    }

    public function test_moving_to_a_category_to_entities_produces_event(): void
    {
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111111', 'product'), // variant product
                        $this->findESIdFor('watch', 'product'), // product
                        $this->findESIdFor('apollon_yellow', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'move_to_category',
            'actions' => [
                [
                    'field' => 'categories',
                    'value' => ['supplier_the_tootles'],
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'move_to_category',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);

        $product = $this->getProductWithInternalApi('watch');
        $this->assertCategories(
            ['supplier_the_tootles'],
            $product['categories']
        );
        $variantProduct = $this->getProductWithInternalApi('1111111111');
        $this->assertCategories(
            /** 'master_men_blazers' and 'supplier_zaro' are coming from 'amor' parent */
            ['master_men_blazers', 'supplier_zaro', 'supplier_the_tootles'],
            $variantProduct['categories']
        );
        $productModel = $this->getProductModelWithInternalApi('apollon_yellow');
        $this->assertCategories(
            /** 'master_men_blazers_deals' and 'supplier_zaro' are coming from 'apollon' parent */
            ['master_men_blazers_deals', 'supplier_zaro', 'supplier_the_tootles'],
            $productModel['categories']
        );
    }

    public function test_removing_a_category_to_entities_produces_event(): void
    {
        $this->updateProductWithInternalApi('1111111119', [
            'identifier' => '1111111119',
            'values' => [],
            'categories' => ['print_accessories'],
        ]);
        $this->clearMessengerTransport();

        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111119', 'product'), // variant product
                        $this->findESIdFor('1111111171', 'product'), // product
                        $this->findESIdFor('amor', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'remove_from_category',
            'actions' => [
                [
                    'field' => 'categories',
                    'value' => ['master_men_blazers', 'print_accessories'],
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'remove_from_category',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);

        $product = $this->getProductWithInternalApi('1111111171');
        $this->assertCategories(
            ['master_accessories_bags', 'supplier_zaro'],
            $product['categories']
        );
        $variantProduct = $this->getProductWithInternalApi('1111111119');
        $this->assertCategories(
            ['master_men_blazers_deals', 'supplier_zaro'],
            $variantProduct['categories']
        );
        $productModel = $this->getProductModelWithInternalApi('amor');
        $this->assertCategories(
            ['supplier_zaro'],
            $productModel['categories']
        );
    }

    private function assertCategories(array $expected, array $actual): void
    {
        $this->assertCount(count($expected), $actual);
        foreach ($expected as $category) {
            $this->assertContains($category, $actual);
        }
    }
}
