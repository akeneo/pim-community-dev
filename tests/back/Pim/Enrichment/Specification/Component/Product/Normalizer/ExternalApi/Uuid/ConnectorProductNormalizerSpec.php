<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\Uuid;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\Uuid\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

class ConnectorProductNormalizerSpec extends ObjectBehavior
{
    // TODO tests kos
    private const PRODUCT_UUID_LIST = [
        'product_code_1' => 'ac2120f5-8241-4e33-a76f-c248231d0605',
        'product_code_4' => '68ceced9-7d92-475d-8fd3-200a2db83169',
        'product_identifier_1' => 'e46c7f42-c449-4219-bf69-d608ebd07d9b',
    ];

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
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
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
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1']],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [self::PRODUCT_UUID_LIST['product_code_4']],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3']
                ]
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'uuid' => self::PRODUCT_UUID_LIST['product_identifier_1'],
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
            new QualityScoreCollection([
                'ecommerce' => [
                    'en_US' => new QualityScore('B', 81),
                    'fr_FR' => new QualityScore('C', 73),
                ],
            ]),
            new ProductCompletenessCollection(
                Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
                [
                    new ProductCompleteness('ecommerce', 'en_US', 10, 5),
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
                ]
            )
        );

        $connector2 = new ConnectorProduct(
            Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
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
            null,
            null
        );

        $connector3 = new ConnectorProduct(
            Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'),
            'identifier_3',
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
            null,
            new ProductCompletenessCollection(Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'), [])
        );

        $this->normalizeConnectorProductList(
            new ConnectorProductList(3, [$connector1, $connector2, $connector3])
        )->shouldBeLike([
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
                        'products' => [self::PRODUCT_UUID_LIST['product_code_1']],
                        'product_models' => [],
                        'groups' => ['group_code_2']
                    ],
                    'UPSELL' => [
                        'products' => [self::PRODUCT_UUID_LIST['product_code_4']],
                        'product_models' => ['product_model_5'],
                        'groups' => ['group_code_3']
                    ]
                ],
                'quantified_associations' => [
                    'PRODUCT_SET' => [
                        'products' => [
                            [
                                'uuid' => self::PRODUCT_UUID_LIST['product_identifier_1'],
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
                'completenesses' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 50],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 90],
                ]
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
            [
                'identifier' => 'identifier_3',
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
                'completenesses' => (object) [],
            ],
        ]);
    }

    function it_normalize_a_single_connection_product()
    {
        $connector = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
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
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1']],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'uuid' => self::PRODUCT_UUID_LIST['product_identifier_1'],
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
            null,
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
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1']],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
            ],
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'uuid' => self::PRODUCT_UUID_LIST['product_identifier_1'],
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
