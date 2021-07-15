<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class MassEditAttributeValueOfEntitiesEndToEnd extends AbstractMassEditEndToEnd
{
    public function test_editing_attribute_value_of_entities_produces_event(): void
    {
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        /*
                         * 0 description
                         * 1 variation_name
                         */
                        $this->findESIdFor('braided-hat-m', 'product'), // variant product
                        /*
                         * 1 description
                         * 0 variation_name
                         */
                        $this->findESIdFor('watch', 'product'), // product
                        /*
                         * 1 description
                         * 4 sub product models with variation_name
                         */
                        $this->findESIdFor('apollon', 'product_model'),
                    ],
                    'context' => [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
            ],
            'jobInstanceCode' => 'edit_common_attributes',
            'actions' => [
                [
                    'attribute_channel' => 'ecommerce',
                    'attribute_locale' => 'en_US',
                    'ui_locale' => 'en_US',
                    'normalized_values' => [
                        'description' => [
                            [
                                'locale' => 'en_US',
                                'scope' => 'ecommerce',
                                'data' => '<p>another description</p>'
                            ],
                        ],
                        'variation_name' => [
                            [
                                'locale' => 'en_US',
                                'scope' => null,
                                'data' => 'Another Braided hat M'
                            ],
                        ],
                    ],
                ],
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'edit_common',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(5, ProductModelUpdated::class);

        $expectedDescription = [
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'data' => '<p>another description</p>'
        ];
        $expectedVariationName = [
            'locale' => 'en_US',
            'scope' => null,
            'data' => 'Another Braided hat M'
        ];
        $product = $this->getProductWithInternalApi('watch');
        $this->assertContains($expectedDescription, $product['values']['description']);

        $variantProduct = $this->getProductWithInternalApi('braided-hat-m');
        $this->assertContains($expectedVariationName, $variantProduct['values']['variation_name']);
        /** description is handled by the product model, it should not have been changed */
        $this->assertContains([
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'data' => 'Braided hat '
        ], $variantProduct['values']['description']);

        $productModel = $this->getProductModelWithInternalApi('apollon');
        $this->assertContains($expectedDescription, $productModel['values']['description']);
    }

    public function test_adding_attribute_value_of_entities_produces_event(): void
    {
        $clothingSize = $this->getFamilyVariantWithInternalApi('clothing_colorsize');
        $clothingSize['variant_attribute_sets'][0]['attributes'][] = 'collection';
        $this->updateFamilyVariantWithInternalApi('clothing_colorsize', $clothingSize);
        $this->clearMessengerTransport();
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        // Parent is the "amor" product model that is in "clothing_colorsize" family variant
                        $this->findESIdFor('1111111111', 'product'), // variant product
                        $this->findESIdFor('watch', 'product'), // product
                        $this->findESIdFor('brogueshoe', 'product_model'),
                    ],
                    'context' => [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
            ],
            'jobInstanceCode' => 'add_attribute_value',
            'actions' => [
                [
                    'attribute_channel' => 'ecommerce',
                    'attribute_locale' => 'en_US',
                    'ui_locale' => 'en_US',
                    'normalized_values' => [
                        'collection' => [
                            [
                                'locale' => null,
                                'scope' => null,
                                'data' => ['winter_2016']
                            ],
                        ],
                    ],
                ],
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'add_attribute_value',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);

        $product = $this->getProductWithInternalApi('watch');
        $this->assertContains([
            'locale' => null,
            'scope' => null,
            'data' => ['winter_2016']
        ], $product['values']['collection'], 'product');

        $variantProduct = $this->getProductWithInternalApi('1111111111');
        $this->assertContains([
            'locale' => null,
            'scope' => null,
            'data' => ['summer_2016', 'winter_2016']
        ], $variantProduct['values']['collection'], 'variant product');

        $productModel = $this->getProductModelWithInternalApi('brogueshoe');
        $this->assertContains([
            'locale' => null,
            'scope' => null,
            'data' => ['summer_2016', 'winter_2016']
        ], $productModel['values']['collection']);
    }
}
