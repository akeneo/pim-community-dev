<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModelSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            1,
            'code',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            null,
            'family',
            'family_variant',
            ['workflow_status' => 'in_progress'],
            [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2'],
                ],
                'UPSELL' => [
                    'products' => ['product_code_4'],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3'],
                ],
            ],
            [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
                        ['identifier' => 'product_2', 'quantity' => 2, 'uuid' => '59a498db-f7d1-4364-8451-e573ebdde55b'],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 3],
                        ['identifier' => 'product_model_2', 'quantity' => 4],
                    ],
                ],
                'PRODUCT_SET1' => [
                    'products' => [
                        ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
                        ['identifier' => 'product_3', 'quantity' => 9, 'uuid' => 'ad7b6aff-a8b0-488d-aec7-ae6483b16b60'],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_1', 'quantity' => 2],
                        ['identifier' => 'product_model_3', 'quantity' => 3],
                    ],
                ],
            ],
            ['category_code_1', 'category_code_2'],
            new ReadValueCollection(
                [
                    ScalarValue::value('text', 'some text'),
                    ScalarValue::localizableValue('description', 'an English description', 'en_US'),
                    ScalarValue::localizableValue('description', 'une description en français', 'fr_FR'),
                ]
            ),
            null
        );
    }

    function it_is_a_connector_product_model()
    {
        $this->shouldHaveType(ConnectorProductModel::class);
    }

    function it_gets_associated_product_identifiers()
    {
        $this->associatedProductIdentifiers()->shouldBeLike(['product_code_1', 'product_code_4']);
    }

    function it_gets_associated_product_model_codes()
    {
        $this->associatedProductModelCodes()->shouldBeLike(['product_model_5']);
    }

    function it_gets_associated_with_quantity_product_uuids()
    {
        $this->associatedWithQuantityProductUuids()->shouldBeLike([
            '9e031351-dc02-457a-bb82-00339ab7b691',
            '59a498db-f7d1-4364-8451-e573ebdde55b',
            'ad7b6aff-a8b0-488d-aec7-ae6483b16b60',
        ]);
    }

    function it_gets_associated_with_quantity_product_model_codes()
    {
        $this->associatedWithQuantityProductModelCodes()->shouldBeLike(['product_model_1', 'product_model_2', 'product_model_3']);
    }

    function it_filters_by_category_codes()
    {
        $connectorProductModel = $this->filterByCategoryCodes(['category_code_3', 'category_code_2']);
        $connectorProductModel->categoryCodes()->shouldBeLike(['category_code_2']);
    }

    function it_filters_with_empty_array_of_category_codes()
    {
        $connectorProduct = $this->filterByCategoryCodes([]);

        $connectorProduct->categoryCodes()->shouldReturn([]);
    }

    function it_filters_associated_products_by_product_identifiers()
    {
        $connectorProductModel = $this->filterAssociatedProductsByProductIdentifiers(
            ['product_code_4', 'product_code_6']
        );
        $connectorProductModel->associatedProductIdentifiers()->shouldBeLike(['product_code_4']);
    }

    function it_filters_associated_products_with_empty_array_of_product_identifiers()
    {
        $connectorProductModel = $this->filterAssociatedProductsByProductIdentifiers([]);
        $connectorProductModel->associatedProductIdentifiers()->shouldReturn([]);
    }

    function it_filters_associated_product_models_by_codes()
    {
        $connectorProductModel = $this->filterAssociatedProductModelsByProductModelCodes(
            ['product_model_1', 'product_model_2']
        );
        $connectorProductModel->associatedProductModelCodes()->shouldReturn([]);
    }

    function it_filters_associated_product_models_with_empty_array_of_codes()
    {
        $connectorProductModel = $this->filterAssociatedProductModelsByProductModelCodes([]);
        $connectorProductModel->associatedProductModelCodes()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_products_with_empty_array_of_product_identifiers()
    {
        $connectorProductModel = $this->filterAssociatedWithQuantityProductsByProductUuids([]);
        $connectorProductModel->associatedWithQuantityProductUuids()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_product_by_uuid()
    {
        $connectorProductModel = $this->filterAssociatedWithQuantityProductsByProductUuids(
            ['9e031351-dc02-457a-bb82-00339ab7b691', '59a498db-f7d1-4364-8451-e573ebdde55b']
        );

        $connectorProductModel->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
                    ['identifier' => 'product_2', 'quantity' => 2, 'uuid' => '59a498db-f7d1-4364-8451-e573ebdde55b'],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_1', 'quantity' => 3],
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
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
        $connectorProductModel = $this->filterAssociatedWithQuantityProductModelsByProductModelCodes([]);
        $connectorProductModel->associatedWithQuantityProductModelCodes()->shouldReturn([]);
    }

    function it_filters_associated_with_quantity_product_models_by_codes()
    {
        $connectorProductModel = $this->filterAssociatedWithQuantityProductModelsByProductModelCodes(
            ['product_model_2', 'product_model_4']
        );

        $connectorProductModel->quantifiedAssociations()->shouldReturn([
            'PRODUCT_SET' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 1, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
                    ['identifier' => 'product_2', 'quantity' => 2, 'uuid' => '59a498db-f7d1-4364-8451-e573ebdde55b'],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_2', 'quantity' => 4],
                ],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'product_1', 'quantity' => 2, 'uuid' => '9e031351-dc02-457a-bb82-00339ab7b691'],
                    ['identifier' => 'product_3', 'quantity' => 9, 'uuid' => 'ad7b6aff-a8b0-488d-aec7-ae6483b16b60'],
                ],
                'product_models' => [],
            ],
        ]);
    }

    function it_filters_values_by_attribute_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(
            ['text', 'another_attribute'],
            ['en_US', 'fr_FR']
        );

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection([ScalarValue::value('text', 'some text')])
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
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(
            ['text', 'description'],
            ['en_US']
        );

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection(
                [
                    ScalarValue::value('text', 'some text'),
                    ScalarValue::localizableValue('description', 'an English description', 'en_US'),
                ]
            )
        );
    }

    function it_filters_values_by_empty_list_of_locale_codes()
    {
        $connectorProduct = $this->filterValuesByAttributeCodesAndLocaleCodes(
            ['text', 'description'],
            []
        );

        $connectorProduct->values()->shouldBeLike(
            new ReadValueCollection(
                [
                    ScalarValue::value('text', 'some text'),
                ]
            )
        );
    }

    function it_filters_values_by_attribute_codes_and_locale_codes()
    {
        $connectorProductModel = $this->filterValuesByAttributeCodesAndLocaleCodes(['description'], ['fr_FR']);
        $connectorProductModel->values()->shouldBeLike(
            new ReadValueCollection(
                [
                    ScalarValue::localizableValue('description', 'une description en français', 'fr_FR'),
                ]
            )
        );
    }

    function it_can_add_metadata()
    {
        $connectorProductModel = $this->addMetadata('some_metadata', ['key' => 'value']);
        $connectorProductModel->metadata()->shouldBeLike(
            [
                'workflow_status' => 'in_progress',
                'some_metadata' => ['key' => 'value'],
            ]
        );
    }

    function it_can_be_built_with_quality_scores(): void
    {
        $qualityScores = new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('A', 98),
                'fr_FR' => new QualityScore('B', 87),
            ]
        ]);
        $connectorProductModel = $this->buildWithQualityScores($qualityScores);

        $connectorProductModel->qualityScores()->shouldReturn($qualityScores);
    }
}
