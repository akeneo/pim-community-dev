<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Fixture;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use AkeneoTest\Pim\Enrichment\Integration\Fixture;

/**
 * Load variant product tree. Those fixtures are used to check the FilterCompletess filter for product and product model.
 */
class CompletenessFilter
{
    /** @var Client */
    private $client;

    /** @var Fixture\EntityBuilder */
    private $entityBuilder;

    /**
     * @param EntityBuilder $entityBuilder
     */
    public function __construct(Client $client, Fixture\EntityBuilder $entityBuilder)
    {
        $this->client = $client;
        $this->entityBuilder = $entityBuilder;
    }

    /**
     * Family:
     * +-------------------------------------------------------------------------------------+-------------------+-------------------+
     * |    Code  |                             Attribute                                    | Requirement en_US | Requirement fr_FR |
     * +----------+--------------------------------------------------------------------------+-------------------+-------------------+
     * | familyA3 |  a_simple_select,a_yes_no,a_text,sku,a_localized_and_scopable_text_area  | All Attributes    | All Attributes    |
     * +-------------------------------------------------------------------------------------+-------------------+-------------------+
     *
     * Variant family:
     * +-------------------------------------------------------------------------------------------------------+
     * |          Code            |  Axes           |  Level |             Attribute                           |
     * +--------------------------+-----------------+----------------------------------------------------------+
     * | two_level_family_variant |        -        | Common | -                                               |
     * | two_level_family_variant | a_simple_select | 1      |a_text                                           |
     * | two_level_family_variant | a_yes_no        | 2      |sku,a_localized_and_scopable_text_area           |
     * +-------------------------------------------------------------------------------------------------------+
     * | one_level_family_variant |        -        | Common | -                                               |
     * | one_level_family_variant | a_simple_select | Common |sku,a_localized_and_scopable_text_area, a_yes_no |
     * +-+-----------------------------------------------------------------------------------------------------+
     *
     * Completeness:
     * +--------------------------------------------------------------------------+------------------------------+------------------------------+
     * |                   | Ecommerce |         Tablet         | Ecommerce China |                              |                              |
     * |  Variant product  |   en_US   |  fr_FR | en_US | de_DE |  en_US | zh_CN  |       Sub product model      |      Root product model      |
     * +-------------------+-----------+--------+-------+-------+--------+--------+------------------------------+------------------------------+
     * | variant_product_1 |  100%     |  75%   | 100%  |  75%  |  100%  |  100%  | sub_product_model            | root_product_model_two_level |
     * | variant_product_2 |  100%     |  100%  | 75%   |  75%  |  100%  |  100%  | sub_product_model            | root_product_model_two_level |
     * | variant_product_3 |  100%     |  100%  | 100%  |  75%  |  100%  |  100%  | root_product_model_one_level |              -               |
     * | variant_product_4 |  100%     |  100%  | 75%   |  75%  |  100%  |  100%  | root_product_model_one_level |              -               |
     * | simple_product    |  100%     |  75%   | 75 %  |  75%  |  100%  |  100%  |              -               |              -               |
     * +--------------------------------------------------------------------------+------------------------------+------------------------------+
     *
     * To check that that numbers are right, here is a SQL query:
     *
     * SELECT p.identifier, ch.code, lo.code, FLOOR(100 * (co.required_count - co.missing_count) / co.required_count) AS ratio
     * FROM pim_catalog_completeness AS co
     * INNER JOIN pim_catalog_product AS p ON co.product_id = p.id
     * INNER JOIN pim_catalog_channel AS ch ON ch.id = co.channel_id
     * INNER JOIN pim_catalog_locale AS lo ON lo.id = co.locale_id
     */
    public function loadProductModelTree(): void
    {
        $this->entityBuilder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );

        $this->entityBuilder->createFamilyVariant(
            [
                'code' => 'one_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text', 'sku', 'a_localized_and_scopable_text_area', 'a_yes_no'],
                    ],
                ],
            ]
        );

        $rootProductModel = $this->entityBuilder->createProductModel(
            'root_product_model_two_level',
            'two_level_family_variant',
            null,
            []
        );

        $subProductModel = $this->entityBuilder->createProductModel(
            'sub_product_model',
            'two_level_family_variant',
            $rootProductModel,
            [
                'values' => [
                    'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                ],
            ]
        );

        $this->entityBuilder->createVariantProduct(
            'variant_product_1',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_1', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ],
                ],
            ]
        );

        $this->entityBuilder->createVariantProduct(
            'variant_product_2',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_2', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $rootProductModelOneLevel = $this->entityBuilder->createProductModel(
            'root_product_model_one_level',
            'one_level_family_variant',
            null,
            []
        );

        $this->entityBuilder->createVariantProduct(
            'variant_product_3',
            'familyA3',
            'one_level_family_variant',
            $rootProductModelOneLevel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_3', 'locale' => null, 'scope' => null]],
                    'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $this->entityBuilder->createVariantProduct(
            'variant_product_4',
            'familyA3',
            'one_level_family_variant',
            $rootProductModelOneLevel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_4', 'locale' => null, 'scope' => null]],
                    'a_simple_select' => [['data' => 'optionB', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $this->entityBuilder->createProduct('simple_product', 'familyA3', [
            'values' => [
                'sku' => [['data' => 'simple_product', 'locale' => null, 'scope' => null]],
                'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->client->refreshIndex();
    }
}
