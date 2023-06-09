<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

class ConnectorProductWithUuidNormalizerSpec extends ObjectBehavior
{
    function let(
        ProductValueNormalizer $productValueNormalizer,
        RouterInterface $router
    ) {
        $this->beConstructedWith(
            new ValuesNormalizer($productValueNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer()
        );
    }

    function it_is_a_normalizer_of_a_list_of_connector_products()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductWithUuidNormalizer::class);
    }

    function it_normalizes_a_list_of_products(
        ProductValueNormalizer $productValueNormalizer
    ) {
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
                    'products' => [
                        ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1'],
                        ['uuid' => '905addae-b005-41c4-a277-9fe8804f43f5', 'identifier' => null],
                    ],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [['uuid' => '0c14f70a-18c0-40d1-ab25-9994dd17c486', 'identifier' => 'product_code_4']],
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
                            'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da',
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
                'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
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
                        'products' => ['95341071-a0dd-47c6-81b1-315913952c43', '905addae-b005-41c4-a277-9fe8804f43f5'],
                        'product_models' => [],
                        'groups' => ['group_code_2']
                    ],
                    'UPSELL' => [
                        'products' => ['0c14f70a-18c0-40d1-ab25-9994dd17c486'],
                        'product_models' => ['product_model_5'],
                        'groups' => ['group_code_3']
                    ]
                ],
                'quantified_associations' => [
                    'PRODUCT_SET' => [
                        'products' => [
                            [
                                'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da',
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
                'uuid' => 'd9f573cc-8905-4949-8151-baf9d5328f26',
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
                'uuid' => 'fdf6f091-3f75-418f-98af-8c19db8b0000',
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
                'completenesses' => [],
            ],
        ]);
    }

    function it_normalize_a_single_connection_product(
        ProductValueNormalizer $productValueNormalizer
    ) {
        $skuValue = IdentifierValue::value('sku', true, 'identifier1');
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
                    'products' => [['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1']],
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
                            'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da',
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
            new ReadValueCollection([
                $skuValue
            ]),
            null,
            null
        );

        $productValueNormalizer
            ->normalize($skuValue, 'standard')
            ->shouldBeCalled()
            ->willReturn(['normalizedIdentifier']);

        $this->normalizeConnectorProduct($connector)->shouldBeLike([
            'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'created' => '2019-04-23T15:55:50+00:00',
            'updated' => '2019-04-25T15:55:50+00:00',
            'enabled' => true,
            'family' => 'family_code',
            'categories' => ['category_code_1', 'category_code_2'],
            'groups' => ['group_code_1', 'group_code_2'],
            'parent' => 'parent_product_model_code',
            'values' => [
                'sku' => [['normalizedIdentifier']],
            ],
            'associations' => [
                'X_SELL' => [
                    'products' => ['95341071-a0dd-47c6-81b1-315913952c43'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
            ],
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        [
                            'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da',
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
