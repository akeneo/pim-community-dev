<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ConnectorProductSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            'identifier',
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
                        ['uuid' => '11cd5db0-c69f-4f12-819c-ab55240d5ac3', 'identifier' => 'product_code_1'],
                        ['uuid' => 'b1c75d2b-cb38-4f1f-9f63-512220c3d3ef', 'identifier' => 'product_code_2'],
                    ],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [
                        ['uuid' => '15f19a58-535d-4348-9a97-1eb0fb57ca3f', 'identifier' => 'product_code_4'],
                    ],
                    'product_models' => ['product_model_5', 'product_model_6'],
                    'groups' => ['group_code_3']
                ]
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                        ['identifier' => 'product_2', 'quantity' => 2, 'uuid' => '894631da-832f-4818-a2ae-44d70c16e679'],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 3],
                        ['identifier' => 'product_model_2', 'quantity' => 4],
                    ],
                ],
                'PRODUCT_SET1' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                        ['identifier' => 'product_3', 'quantity' => 9, 'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da'],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 2],
                        ['identifier' => 'product_model_3', 'quantity' => 3],
                    ],
                ],
            ],
            [],
            new ReadValueCollection([
                ScalarValue::value('attribute_code_1', 'data'),
                ScalarValue::localizableValue('attribute_code_2', 'data', 'en_US'),
                ScalarValue::localizableValue('attribute_code_2', 'data', 'fr_FR'),
                OptionValue::value('simple_select', 'Option_1'),
                OptionsValue::value('multi_select', ['Option1', 'OPTION2']),
                OptionValue::value('other_simple_select','42'),
                OptionsValue::value('other_multi_select', ['30']),
            ]),
            null,
            null
        );
    }

    function it_is_a_connector_product()
    {
        $this->shouldBeAnInstanceOf(ConnectorProduct::class);
    }

    function it_filters_by_category_codes()
    {
        $connectorProduct = $this->filterByCategoryCodes(['category_code_1', 'category_code_3']);

        $connectorProduct->categoryCodes()->shouldReturn(['category_code_1']);
    }

    function it_filters_with_empty_array_of_category_codes()
    {
        $connectorProduct = $this->filterByCategoryCodes([]);

        $connectorProduct->categoryCodes()->shouldReturn([]);
    }

    function it_filters_associated_products_by_product_uuids()
    {
        $unknownUuid = '2e390798-7c62-4219-8591-85d26581b0f3';
        $connectorProduct = $this->filterAssociatedProductsByProductUuids(['11cd5db0-c69f-4f12-819c-ab55240d5ac3', $unknownUuid]);

        $connectorProduct->associations()->shouldBeLike(
            [
                'X_SELL' => [
                    'products' => [
                        ['uuid' => '11cd5db0-c69f-4f12-819c-ab55240d5ac3', 'identifier' => 'product_code_1'],
                    ],
                    'product_models' => [],
                    'groups' => ['group_code_2'],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => ['product_model_5', 'product_model_6'],
                    'groups' => ['group_code_3']
                ]
            ]
        );
    }

    function it_filters_associated_product_model_by_product_model_codes()
    {
        $connectorProduct = $this->filterAssociatedProductModelsByProductModelCodes(['product_model_5', 'product_model_8']);

        $connectorProduct->associations()->shouldBeLike(
            [
                'X_SELL' => [
                    'products' => [
                        ['uuid' => '11cd5db0-c69f-4f12-819c-ab55240d5ac3', 'identifier' => 'product_code_1'],
                        ['uuid' => 'b1c75d2b-cb38-4f1f-9f63-512220c3d3ef', 'identifier' => 'product_code_2'],
                    ],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [
                        ['uuid' => '15f19a58-535d-4348-9a97-1eb0fb57ca3f', 'identifier' => 'product_code_4']
                    ],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3']
                ]
            ]
        );
    }

    function it_filters_associated_with_quantity_products_with_empty_array_of_product_uuids()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductsByProductUuids([]);
        $connectorProduct->associatedWithQuantityProductUuids()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_product_by_uuid()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductsByProductUuids(
            ['b9c3b775-d6ef-4748-b384-a99a759e469a', '77ff41a7-69fc-4b4a-898c-3117e08e60da']
        );

        $connectorProduct->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_1', 'quantity' => 3],
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                    ['identifier' => 'product_3', 'quantity' => 9, 'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da'],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_1', 'quantity' => 2],
                    ['identifier' => 'product_model_3', 'quantity' => 3],
                ],
            ],
        ]);
    }

    function it_filters_associated_with_quantity_product_models_with_empty_array_of_codes()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductModelsByProductModelCodes([]);
        $connectorProduct->associatedWithQuantityProductModelCodes()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_product_models_by_codes()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductModelsByProductModelCodes(
            ['product_model_2', 'product_model_4']
        );

        $connectorProduct->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                    ['identifier' => 'product_2', 'quantity' => 2, 'uuid' => '894631da-832f-4818-a2ae-44d70c16e679']
                ],
                'product_models' => [
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => 'b9c3b775-d6ef-4748-b384-a99a759e469a'],
                    ['identifier' => 'product_3', 'quantity' => 9, 'uuid' => '77ff41a7-69fc-4b4a-898c-3117e08e60da']
                ],
                'product_models' => [],
            ],
        ]);
    }

    function it_filters_values_by_attribute_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(['attribute_code_1', 'attribute_code_4'], ['en_US', 'fr_FR']);

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection([ScalarValue::value('attribute_code_1', 'data')])
        );
    }

    function it_filters_values_by_empty_list_of_attribute_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes([], ['en_US', 'fr_FR']);

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection([])
        );
    }

    function it_filters_values_by_locale_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(['attribute_code_1', 'attribute_code_2'], ['en_US']);

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection([
                ScalarValue::value('attribute_code_1', 'data'),
                ScalarValue::localizableValue('attribute_code_2', 'data', 'en_US')
            ])
        );
    }

    function it_filters_values_by_empty_list_of_locale_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(['attribute_code_1', 'attribute_code_2'], []);

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection([
                ScalarValue::value('attribute_code_1', 'data'),
            ])
        );
    }

    function it_gets_associated_product_identifiers()
    {
        $this->associatedProductModelCodes()->shouldReturn(['product_model_5', 'product_model_6']);
    }


    function it_gets_associated_with_quantity_product_uuids()
    {
        $this->associatedWithQuantityProductUuids()->shouldBeLike([
            'b9c3b775-d6ef-4748-b384-a99a759e469a',
            '894631da-832f-4818-a2ae-44d70c16e679',
            '77ff41a7-69fc-4b4a-898c-3117e08e60da',
        ]);
    }

    function it_gets_associated_with_quantity_product_model_codes()
    {
        $this->associatedWithQuantityProductModelCodes()->shouldBeLike(['product_model_1', 'product_model_2', 'product_model_3']);
    }

    function it_adds_a_metadata()
    {
        $connectorProduct = $this->addMetadata('key', 'value');

        $connectorProduct->metadata()->shouldReturn(['key' => 'value']);
    }

    function it_returns_empty_array_if_no_association_type_exists()
    {
        $this->beConstructedWith(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            'identifier',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'clothes',
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $this->associatedProductUuids()->shouldReturn([]);
        $this->associatedProductModelCodes()->shouldReturn([]);
        $this->associatedWithQuantityProductUuids()->shouldReturn([]);
        $this->associatedWithQuantityProductModelCodes()->shouldReturn([]);
    }

    function it_returns_the_product_completeness_collection()
    {
        $completenesses = [
            new ProductCompleteness('ecommerce', 'en_US', 10, 5),
            new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
            new ProductCompleteness('print', 'en_US', 4, 0),
        ];
        $completenessCollection = new ProductCompletenessCollection(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), $completenesses);
        $connectorProduct = $this->buildWithCompletenesses($completenessCollection);

        $connectorProduct->completenesses()->shouldReturn($completenessCollection);
    }

    function it_returns_the_option_labels()
    {
        $connectorProductWithLinkedData = $this->buildLinkedData([
            'simple_select' => [
                'option_1' => [
                    'en_US' => 'Code 1',
                    'fr_FR' => 'Option 1',
                ],
            ],
            'multi_select' => [
                'option1' => [
                    'en_US' => 'OPTION NUMBER ONE',
                    'fr_FR' => null,
                ],
                'Option2' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ]
            ],
            'other_simple_select' => [
                '42' => [
                    'en_US' => '42',
                    'fr_FR' => '42',
                ]
            ],
            'other_multi_select' => [
                '42' => [
                    'en_US' => '42',
                    'fr_FR' => '42',
                ],
                '30' => [
                    'en_US' => '30',
                    'fr_FR' => '30',
                ]
            ]
        ]);
        $connectorProductWithLinkedData->shouldBeAnInstanceOf(ConnectorProduct::class);
        /** @var ReadValueCollection $values */
        $connectorProductWithLinkedData->values()->toArray()->shouldBeLike(
            [
                ScalarValue::value('attribute_code_1', 'data'),
                ScalarValue::localizableValue('attribute_code_2', 'data', 'en_US'),
                ScalarValue::localizableValue('attribute_code_2', 'data', 'fr_FR'),
                new OptionValueWithLinkedData(
                    'simple_select',
                    'Option_1',
                    null,
                    null,
                    [
                        'attribute' => 'simple_select',
                        'code' => 'option_1',
                        'labels' => [
                            'en_US' => 'Code 1',
                            'fr_FR' => 'Option 1',
                        ]
                    ]
                ),
                new OptionsValueWithLinkedData(
                    'multi_select',
                    ['Option1', 'OPTION2'],
                    null,
                    null,
                    [
                        'Option1' => [
                            'attribute' => 'multi_select',
                            'code' => 'option1',
                            'labels' => [
                                'en_US' => 'OPTION NUMBER ONE',
                                'fr_FR' => null,
                            ],
                        ],
                        'OPTION2' => [
                            'attribute' => 'multi_select',
                            'code' => 'Option2',
                            'labels' => [
                                'en_US' => null,
                                'fr_FR' => null,
                            ]
                        ]
                    ]
                ),
                new OptionValueWithLinkedData(
                    'other_simple_select',
                    '42',
                    null,
                    null,
                    [
                        'attribute' => 'other_simple_select',
                        'code' => '42',
                        'labels' => [
                            'en_US' => '42',
                            'fr_FR' => '42',
                            ]

                    ]
                ),
                new OptionsValueWithLinkedData(
                    'other_multi_select',
                    ['30'],
                    null,
                    null,
                    [
                        '30' => [
                            'attribute' => 'other_multi_select',
                            'code' => '30',
                            'labels' => [
                                'en_US' => '30',
                                'fr_FR' => '30',
                            ],
                        ]
                    ]
                )
            ]
        );
    }
}
