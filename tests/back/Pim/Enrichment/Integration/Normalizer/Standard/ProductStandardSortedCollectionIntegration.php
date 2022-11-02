<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Common\EntityWithValue\Builder\Product as ProductBuilder;
use Akeneo\Test\Integration\TestCase;

class ProductStandardSortedCollectionIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_price_collection_are_correctly_sorted()
    {
        $product = $this->getProductBuilder()
            ->withUuid('33988891-8cba-47fa-8057-693c740e4a88')
            ->withValue('a_price', [
                ['amount' => '40', 'currency' => 'CNY'],
                ['amount' => '40', 'currency' => 'USD'],
                ['amount' => '40', 'currency' => 'EUR'],
            ])
            ->build();

        $expected = [
            'uuid' => '33988891-8cba-47fa-8057-693c740e4a88',
            'identifier' => 'my-product',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'my-product']
                ],
                'a_price' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            ['amount' => '40.00', 'currency' => 'CNY'],
                            ['amount' => '40.00', 'currency' => 'EUR'],
                            ['amount' => '40.00', 'currency' => 'USD'],
                        ]
                    ]
                ]
            ],
            'created' => null,
            'updated' => null,
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $this->assertStandardFormat($product, $expected);
    }

    public function test_options_are_correctly_sorted()
    {
        $product = $this->getProductBuilder()
            ->withUuid('214cf61f-b1c7-4d56-81ba-a7ba5225c836')
            ->withValue('a_multi_select', ['optionB', 'optionA'])
            ->build();

        $expected = [
            'uuid' => '214cf61f-b1c7-4d56-81ba-a7ba5225c836',
            'identifier' => 'my-product',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'my-product']
                ],
                'a_multi_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => ['optionA', 'optionB']
                    ]
                ]
            ],
            'created' => null,
            'updated' => null,
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $this->assertStandardFormat($product, $expected);
    }

    public function test_multi_ref_data_are_correctly_sorted()
    {
        $product = $this->getProductBuilder()
            ->withUuid('37e8e555-6379-487c-9376-47a35d45b876')
            ->withValue('a_ref_data_multi_select', ['zibeline', 'tapestry', 'brilliantine'])
            ->build();

        $expected = [
            'uuid' => '37e8e555-6379-487c-9376-47a35d45b876',
            'identifier' => 'my-product',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'my-product']
                ],
                'a_ref_data_multi_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => ['brilliantine', 'tapestry', 'zibeline']
                    ]
                ]
            ],
            'created' => null,
            'updated' => null,
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $this->assertStandardFormat($product, $expected);
    }

    private function assertStandardFormat(ProductInterface $product, array $expected): void
    {
        $serializer = $this->get('pim_standard_format_serializer');
        $result = $serializer->normalize($product, 'standard');

        $this->assertSame($expected, $result);
    }

    private function getProductBuilder(): ProductBuilder
    {
        return $this->get('akeneo_integration_tests.catalog.product.builder');
    }
}
