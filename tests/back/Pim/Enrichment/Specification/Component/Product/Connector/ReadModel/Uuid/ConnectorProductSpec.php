<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ConnectorProductSpec extends ObjectBehavior
{
    // TODO tests kos
    private const PRODUCT_UUID_LIST = [
        'product_code_1' => 'ac2120f5-8241-4e33-a76f-c248231d0605',
        'product_code_2' => '8a420a54-4f50-4935-b9ea-75737da6af91',
        'product_code_4' => '68ceced9-7d92-475d-8fd3-200a2db83169',
        'product_code_7' => 'bd63aa0f-b7e3-4cf2-901d-877d6db24ce5',
            'product_1' => 'e46c7f42-c449-4219-bf69-d608ebd07d9b',
            'product_2' => 'ed91c3ef-0354-41c5-b583-adff1fa2fee5',
            'product_3' => '52504e85-a0e9-482a-81b1-5de594df4d3d',
            'product_4' => 'e76294bd-fbc1-4982-b24a-c14a5cefee88',
    ];

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
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1'], self::PRODUCT_UUID_LIST['product_code_2']],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [self::PRODUCT_UUID_LIST['product_code_4']],
                    'product_models' => ['product_model_5', 'product_model_6'],
                    'groups' => ['group_code_3']
                ]
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => self::PRODUCT_UUID_LIST['product_1'], 'quantity' => 1],
                        ['uuid' => self::PRODUCT_UUID_LIST['product_2'], 'quantity' => 2]
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 3],
                        ['identifier' => 'product_model_2', 'quantity' => 4],
                    ],
                ],
                'PRODUCT_SET1' => [
                    'products' => [
                        ['uuid' => self::PRODUCT_UUID_LIST['product_1'], 'quantity' => 2],
                        ['uuid' => self::PRODUCT_UUID_LIST['product_3'], 'quantity' => 9]
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

        $connectorProduct = $this->filterAssociatedProductsByProductUuids([self::PRODUCT_UUID_LIST['product_code_1'], self::PRODUCT_UUID_LIST['product_code_7']]);

        $connectorProduct->associations()->shouldBeLike(
            [
                'X_SELL' => [
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1']],
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
                    'products' => [self::PRODUCT_UUID_LIST['product_code_1'], self::PRODUCT_UUID_LIST['product_code_2']],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => [self::PRODUCT_UUID_LIST['product_code_4']],
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

    function it_filters_associated_with_quantity_product_by_identifier()
    {
        $connectorProduct = $this->filterAssociatedWithQuantityProductsByProductUuids(
            [self::PRODUCT_UUID_LIST['product_2'], self::PRODUCT_UUID_LIST['product_4']]
        );

        $connectorProduct->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['uuid' => self::PRODUCT_UUID_LIST['product_2'], 'quantity' => 2]
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
                    ['uuid' => self::PRODUCT_UUID_LIST['product_1'], 'quantity' => 1],
                    ['uuid' => self::PRODUCT_UUID_LIST['product_2'], 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['uuid' => self::PRODUCT_UUID_LIST['product_1'], 'quantity' => 2],
                    ['uuid' => self::PRODUCT_UUID_LIST['product_3'], 'quantity' => 9]
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
        $this->associatedWithQuantityProductUuids()->shouldBeLike([self::PRODUCT_UUID_LIST['product_1'], self::PRODUCT_UUID_LIST['product_2'], self::PRODUCT_UUID_LIST['product_3']]);
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
}
