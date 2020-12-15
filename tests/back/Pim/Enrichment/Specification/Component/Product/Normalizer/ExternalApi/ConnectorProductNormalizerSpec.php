<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

class ConnectorProductNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueNormalizer $productValuesNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith(new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()), new DateTimeNormalizer());
        $productValuesNormalizer->normalize(Argument::type(ReadValueCollection::class), 'standard')->willReturn([]);
    }

    function it_is_a_normalizer_of_a_list_of_connector_products()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductNormalizer::class);
    }

    function it_normalizes_a_list_of_products()
    {
        $connector1 = new ConnectorProduct(
            1,
            'identifier_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => ['product_code_4'],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3']
                ]
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'identifier' => 'product_identifier_1',
                            'quantity' => 8,
                        ],
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'product_model_5',
                            'quantity' => 2,
                        ],
                    ],
                ],
            ],
            [],
            new ReadValueCollection(),
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(81))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(73))
        );

        $connector2 = new ConnectorProduct(
            1,
            'identifier_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            ['a_metadata' => 'viande'],
            new ReadValueCollection(),
            null
        );

        $this->normalizeConnectorProductList(new ConnectorProductList(1, [$connector1, $connector2]))->shouldBeLike([
            [
                'identifier' => 'identifier_1',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => 'family_code',
                'categories' => ['category_code_1', 'category_code_2'],
                'groups' => ['group_code_1', 'group_code_2'],
                'parent' => 'parent_product_model_code',
                'values' => (object) [],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['product_code_1'],
                        'product_models' => [],
                        'groups' => ['group_code_2']
                    ],
                    'UPSELL' => [
                        'products' => ['product_code_4'],
                        'product_models' => ['product_model_5'],
                        'groups' => ['group_code_3']
                    ]
                ],
                'quantified_associations' => [
                    'PRODUCT_SET' => [
                        'products' => [
                            [
                                'identifier' => 'product_identifier_1',
                                'quantity' => 8,
                            ],
                        ],
                        'product_models' => [
                            [
                                'identifier' => 'product_model_5',
                                'quantity' => 2,
                            ],
                        ],
                    ],
                ],
                'quality_scores' => [
                     ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'B'],
                     ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'C'],
                ],
            ],
            [
                'identifier' => 'identifier_2',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => (object) [],
                'associations' => (object) [],
                'quantified_associations' => (object) [],
                'metadata' => ['a_metadata' => 'viande'],
            ],
        ]);
    }

    function it_normalize_a_single_connection_product()
    {
        $connector = new ConnectorProduct(
            1,
            'identifier_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'identifier' => 'product_identifier_1',
                            'quantity' => 8,
                        ],
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'product_model_5',
                            'quantity' => 2,
                        ],
                    ],
                ],
            ],
            [],
            new ReadValueCollection(),
            null
        );

        $this->normalizeConnectorProduct($connector)->shouldBeLike([
            'identifier' => 'identifier_1',
            'created' => '2019-04-23T15:55:50+00:00',
            'updated' => '2019-04-25T15:55:50+00:00',
            'enabled' => true,
            'family' => 'family_code',
            'categories' => ['category_code_1', 'category_code_2'],
            'groups' => ['group_code_1', 'group_code_2'],
            'parent' => 'parent_product_model_code',
            'values' => (object) [],
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
            ],
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'identifier' => 'product_identifier_1',
                            'quantity' => 8,
                        ],
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'product_model_5',
                            'quantity' => 2,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
