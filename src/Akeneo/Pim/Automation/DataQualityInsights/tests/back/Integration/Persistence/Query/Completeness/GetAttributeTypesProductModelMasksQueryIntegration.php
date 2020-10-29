<?php


namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetNonRequiredProductModelAttributesMaskQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class GetAttributeTypesProductModelMasksQueryIntegration extends CompletenessTestCase
{

    public function test_it_retrieves_image_attributes_for_a_product_model_with_one_level_of_variation()
    {
        $this->givenCurrencyForChannel([['code' => 'ecommerce', 'currencies' => ['USD']]]);
        $this->givenChannels([['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]]);

        $this->givenADeactivatedAttributeGroup('erp');
        $this->givenAttributes([
            ['code' => 'a_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_second_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'an_image', 'type' => AttributeTypes::IMAGE],
            ['code' => 'a_second_image', 'type' => AttributeTypes::IMAGE, 'scopable' => true],
            ['code' => 'a_third_image', 'type' => AttributeTypes::IMAGE, 'localizable' => true],
            ['code' => 'an_us_image', 'type' => AttributeTypes::IMAGE, 'available_locales' => ['en_US']],
            ['code' => 'a_fr_image', 'type' => AttributeTypes::IMAGE, 'available_locales' => ['fr_FR']],
            ['code' => 'a_deactivated_image', 'type' => AttributeTypes::IMAGE, 'group' => 'erp'],
            ['code' => 'a_variation_axis_level_1', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_text',
                    'a_second_text',
                    'an_image',
                    'a_second_image',
                    'a_third_image',
                    'an_us_image',
                    'a_fr_image',
                    'a_deactivated_image',
                    'a_variation_axis_level_1',
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_text',
                        'an_image',
                        'a_variation_axis_level_1',
                    ],
                    'tablet' => [
                        'sku',
                        'a_text',
                        'a_second_image',
                        'a_variation_axis_level_1',
                    ],
                ]
            ],
        ]);

        $this->givenAFamilyVariant([
            'code' => 'familyA_variant1',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_variation_axis_level_1'],
                    'attributes' => ['a_variation_axis_level_1', 'sku', 'a_text', 'an_image', 'an_us_image'],
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModel('a_product_model', 'familyA_variant1');
        $attributesMask = $this->get('akeneo.pim.automation.data_quality_insights.query.product_model.get_enrichment_images_masks')->execute($productModelId);

        $ecommerceEnUsMask = $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $attributesMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $attributesMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'a_second_image-ecommerce-<all_locales>',
            'a_third_image-<all_channels>-en_US',
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'a_second_image-tablet-<all_locales>',
            'a_third_image-<all_channels>-en_US',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing([
            'a_fr_image-<all_channels>-<all_locales>',
            'a_second_image-tablet-<all_locales>',
            'a_third_image-<all_channels>-fr_FR',
        ], $tabletFrFr->mask());
    }

    public function test_it_retrieves_image_attributes_for_a_product_model_with_two_levels_of_variation()
    {
        $this->givenChannels([['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]]);

        $this->givenADeactivatedAttributeGroup('erp');
        $this->givenAttributes([
            ['code' => 'a_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_second_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'an_image', 'type' => AttributeTypes::IMAGE],
            ['code' => 'a_second_image', 'type' => AttributeTypes::IMAGE, 'scopable' => true],
            ['code' => 'a_third_image', 'type' => AttributeTypes::IMAGE, 'localizable' => true],
            ['code' => 'an_us_image', 'type' => AttributeTypes::IMAGE, 'available_locales' => ['en_US']],
            ['code' => 'a_fr_image', 'type' => AttributeTypes::IMAGE, 'available_locales' => ['fr_FR']],
            ['code' => 'a_deactivated_image', 'type' => AttributeTypes::IMAGE, 'group' => 'erp'],
            ['code' => 'a_variation_axis_level_1', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
            ['code' => 'a_variation_axis_level_2', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_text',
                    'a_second_text',
                    'an_image',
                    'a_second_image',
                    'a_third_image',
                    'an_us_image',
                    'a_fr_image',
                    'a_deactivated_image',
                    'a_variation_axis_level_1',
                    'a_variation_axis_level_2',
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_text',
                        'an_image',
                        'a_variation_axis_level_1',
                        'a_variation_axis_level_2',
                    ],
                ]
            ],
        ]);

        $this->givenAFamilyVariant([
            'code' => 'familyA_variant2',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_variation_axis_level_1'],
                    'attributes' => ['a_variation_axis_level_1', 'a_second_text', 'a_second_image', 'an_us_image'],
                ],
                [
                    'level' => 2,
                    'axes' => ['a_variation_axis_level_2'],
                    'attributes' => ['a_variation_axis_level_2', 'sku', 'a_third_image'],
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModel('a_root_product_model', 'familyA_variant2');
        $attributesMask = $this->get('akeneo.pim.automation.data_quality_insights.query.product_model.get_enrichment_images_masks')->execute($productModelId);

        $this->assertEqualsCanonicalizing([
            'an_image-<all_channels>-<all_locales>',
        ], $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US')->mask());

        $this->assertEqualsCanonicalizing([
            'an_image-<all_channels>-<all_locales>',
            'a_fr_image-<all_channels>-<all_locales>',
        ], $attributesMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR')->mask());

        $subProductModelId = $this->givenASubProductModel('a_sub_product_model', 'familyA_variant2', 'a_root_product_model');
        $attributesMask = $this->get('akeneo.pim.automation.data_quality_insights.query.product_model.get_enrichment_images_masks')->execute($subProductModelId);

        $this->assertEqualsCanonicalizing([
            'an_image-<all_channels>-<all_locales>',
            'a_second_image-ecommerce-<all_locales>',
            'an_us_image-<all_channels>-<all_locales>',

        ], $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US')->mask());
    }
}
