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
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

class ConnectorProductNormalizerSpec extends ObjectBehavior
{
    const PRODUCT_UUIDS = [
        'identifier_1' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
        'identifier_2' => 'd9f573cc-8905-4949-8151-baf9d5328f26',
        'identifier_3' => 'fdf6f091-3f75-418f-98af-8c19db8b0000',
    ];

    function let(
        ProductValueNormalizer $productValueNormalizer,
        RouterInterface $router,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            new ValuesNormalizer($productValueNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer(),
            $attributeRepository
        );
        $attributeRepository->getIdentifierCode()->willReturn('sku');
    }

    function it_is_a_normalizer_of_a_list_of_connector_products()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductNormalizer::class);
    }

    function it_normalizes_a_list_of_products(
        ProductValueNormalizer $productValueNormalizer
    ) {
        $identifier1 = IdentifierValue::value('sku', true, 'identifier_1');
        $value1 = ScalarValue::value('another_attribute', 'value_1');
        $connector1 = new ConnectorProduct(
            Uuid::fromString(self::PRODUCT_UUIDS['identifier_1']),
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
            new ReadValueCollection([$identifier1, $value1]),
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

        $identifier2 = IdentifierValue::value('sku', true, 'identifier_2');
        $value2 = ScalarValue::value('another_attribute', 'value_2');
        $connector2 = new ConnectorProduct(
            Uuid::fromString(self::PRODUCT_UUIDS['identifier_2']),
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
            new ReadValueCollection([$identifier2, $value2]),
            null,
            null
        );

        $identifier3 = IdentifierValue::value('sku', true, 'identifier_3');
        $value3 = ScalarValue::value('another_attribute', 'value_3');
        $connector3 = new ConnectorProduct(
            Uuid::fromString(self::PRODUCT_UUIDS['identifier_3']),
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
            new ReadValueCollection([$value3, $identifier3]),
            null,
            new ProductCompletenessCollection(Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'), [])
        );

        $productValueNormalizer->normalize($identifier1, 'standard')->shouldBeCalled()->willReturn(['normalizedIdentifier1']);
        $productValueNormalizer->normalize($value1, 'standard')->shouldBeCalled()->willReturn(['normalizedValue1']);
        $productValueNormalizer->normalize($identifier2, 'standard')->shouldBeCalled()->willReturn(['normalizedIdentifier2']);
        $productValueNormalizer->normalize($value2, 'standard')->shouldBeCalled()->willReturn(['normalizedValue2']);
        $productValueNormalizer->normalize($identifier3, 'standard')->shouldBeCalled()->willReturn(['normalizedIdentifier3']);
        $productValueNormalizer->normalize($value3, 'standard')->shouldBeCalled()->willReturn(['normalizedValue3']);

        $this->normalizeConnectorProductList(
            new ConnectorProductList(3, [$connector1, $connector2, $connector3])
        )->shouldBeLike([
            [
                'uuid' => self::PRODUCT_UUIDS['identifier_1'],
                'identifier' => 'identifier_1',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => 'family_code',
                'categories' => ['category_code_1', 'category_code_2'],
                'groups' => ['group_code_1', 'group_code_2'],
                'parent' => 'parent_product_model_code',
                'values' => [
                    'sku' => [['normalizedIdentifier1']],
                    'another_attribute' => [['normalizedValue1']],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['product_code_1', null],
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
                'completenesses' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 50],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 90],
                ]
            ],
            [
                'uuid' => self::PRODUCT_UUIDS['identifier_2'],
                'identifier' => 'identifier_2',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => [
                    'sku' => [['normalizedIdentifier2']],
                    'another_attribute' => [['normalizedValue2']],
                ],
                'associations' => (object) [],
                'quantified_associations' => (object) [],
                'metadata' => ['a_metadata' => 'viande'],
            ],
            [
                'uuid' => self::PRODUCT_UUIDS['identifier_3'],
                'identifier' => 'identifier_3',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => [
                    'sku' => [['normalizedIdentifier3']],
                    'another_attribute' => [['normalizedValue3']],
                ],
                'associations' => (object) [],
                'quantified_associations' => (object) [],
                'metadata' => ['a_metadata' => 'viande'],
                'completenesses' => [],
            ],
        ]);
    }

    function it_normalizes_a_single_connector_product(
        ProductValueNormalizer $productValueNormalizer
    ) {
        $identifier = IdentifierValue::value('sku', true, 'identifier_1');
        $value = ScalarValue::value('another_attribute', 'value');
        $connector = new ConnectorProduct(
            Uuid::fromString(self::PRODUCT_UUIDS['identifier_1']),
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
            new ReadValueCollection([$identifier, $value]),
            null,
            null
        );

        $productValueNormalizer->normalize($identifier, 'standard')->shouldBeCalled()->willReturn(['normalizedIdentifier']);
        $productValueNormalizer->normalize($value, 'standard')->shouldBeCalled()->willReturn(['normalizedValue']);

        $this->normalizeConnectorProduct($connector)->shouldBeLike([
            'uuid' => self::PRODUCT_UUIDS['identifier_1'],
            'identifier' => 'identifier_1',
            'created' => '2019-04-23T15:55:50+00:00',
            'updated' => '2019-04-25T15:55:50+00:00',
            'enabled' => true,
            'family' => 'family_code',
            'categories' => ['category_code_1', 'category_code_2'],
            'groups' => ['group_code_1', 'group_code_2'],
            'parent' => 'parent_product_model_code',
            'values' => [
                'sku' => [['normalizedIdentifier']],
                'another_attribute' => [['normalizedValue']],
            ],
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
