<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;

class ConnectorProductSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            1,
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
                    'products' => ['product_code_1', 'product_code_2'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => ['product_code_4'],
                    'product_models' => ['product_model_5', 'product_model_6'],
                    'groups' => ['group_code_3']
                ]
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 1],
                        ['identifier' => 'product_2', 'quantity' => 2]
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 3],
                        ['identifier' => 'product_model_2', 'quantity' => 4],
                    ],
                ],
                'PRODUCT_SET1' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 2],
                        ['identifier' => 'product_3', 'quantity' => 9]
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
                ScalarValue::localizableValue('attribute_code_2', 'data', 'fr_FR')
            ]),
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

    function it_filters_associated_products_by_product_identifiers()
    {
        $connectorProduct = $this->filterAssociatedProductsByProductIdentifiers(['product_code_1', 'product_code_7']);

        $connectorProduct->associations()->shouldBeLike(
            [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
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
                    'products' => ['product_code_1', 'product_code_2'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => ['product_code_4'],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3']
                ]
            ]
        );
    }

    function it_filters_associated_with_quantity_products_with_empty_array_of_product_identifiers()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductsByProductIdentifiers([]);
        $connectorProduct->associatedWithQuantityProductIdentifiers()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_product_by_identifier()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductsByProductIdentifiers(
            ['product_2', 'product_4']
        );

        $connectorProduct->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['identifier' => 'product_2', 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'product_model_1', 'quantity' => 3],
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [],
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
                    ['identifier' => 'product_1', 'quantity' => 1],
                    ['identifier' => 'product_2', 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 2],
                    ['identifier' => 'product_3', 'quantity' => 9]
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


    function it_gets_associated_with_quantity_product_identifiers()
    {
        $this->associatedWithQuantityProductIdentifiers()->shouldBeLike(['product_1', 'product_2', 'product_3']);
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
            42,
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
        );

        $this->associatedProductIdentifiers()->shouldReturn([]);
        $this->associatedProductModelCodes()->shouldReturn([]);
        $this->associatedWithQuantityProductIdentifiers()->shouldReturn([]);
        $this->associatedWithQuantityProductModelCodes()->shouldReturn([]);
    }
}
