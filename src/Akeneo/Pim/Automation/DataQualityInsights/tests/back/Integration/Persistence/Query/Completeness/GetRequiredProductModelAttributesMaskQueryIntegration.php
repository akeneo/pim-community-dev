<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetRequiredProductModelAttributesMaskQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetRequiredProductModelAttributesMaskQueryIntegration extends CompletenessTestCase
{
    public function test_it_gives_the_required_attributes_masks_for_a_product_model_with_one_level_of_variation()
    {
        $this->givenCurrencyForChannel([['code' => 'ecommerce', 'currencies' => ['USD']]]);
        $this->givenChannels([['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]]);

        $this->givenADeactivatedAttributeGroup('erp');
        $this->givenAttributes([
            ['code' => 'a_non_required_text', 'type' => AttributeTypes::TEXT],
            // A price because the handling is different than other attribute
            ['code' => 'a_price', 'type' => AttributeTypes::PRICE_COLLECTION],
            ['code' => 'a_localizable_non_scopable_price', 'type' => AttributeTypes::PRICE_COLLECTION, 'localizable' => true],
            // Localizable and Scopable things
            ['code' => 'a_non_localizable_non_scopable_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_localizable_non_scopable_text', 'type' => AttributeTypes::TEXT, 'localizable' => true],
            ['code' => 'a_non_localizable_scopable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true],
            ['code' => 'a_localizable_scopable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            // Locale specific things
            ['code' => 'a_non_localizable_non_scopable_locale_specific', 'type' => AttributeTypes::TEXT, 'available_locales' => ['fr_FR']],
            ['code' => 'a_localizable_non_scopable_locale_specific', 'type' => AttributeTypes::TEXT, 'localizable' => true, 'available_locales' => ['en_US']],
            ['code' => 'a_non_localizable_scopable_locale_specific', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'available_locales' => ['fr_FR', 'en_US']],
            ['code' => 'a_localizable_scopable_locale_specific', 'type' => AttributeTypes::TEXT, 'localizable' => true, 'scopable' => true, 'available_locales' => ['fr_FR']],
            // Attributes for the family variant
            ['code' => 'a_required_variant_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_variation_axis', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
            // Attributes required but deactivated from their attribute group
            ['code' => 'a_required_deactivated_text', 'type' => AttributeTypes::TEXT, 'group' => 'erp'],
            ['code' => 'a_required_deactivated_variant_text', 'type' => AttributeTypes::TEXT, 'group' => 'erp'],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_price',
                    'a_localizable_non_scopable_price',
                    'a_non_required_text',
                    'a_non_localizable_non_scopable_text',
                    'a_localizable_non_scopable_text',
                    'a_non_localizable_scopable_text',
                    'a_localizable_scopable_text',
                    'a_non_localizable_non_scopable_locale_specific',
                    'a_localizable_non_scopable_locale_specific',
                    'a_non_localizable_scopable_locale_specific',
                    'a_localizable_scopable_locale_specific',
                    'a_variation_axis',
                    'a_required_variant_text',
                    'a_required_deactivated_text',
                    'a_required_deactivated_variant_text',
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_price',
                        'a_localizable_non_scopable_text',
                        'a_non_localizable_scopable_text',
                        'a_localizable_non_scopable_locale_specific',
                        'a_variation_axis',
                        'a_required_variant_text',
                        'a_required_deactivated_text',
                        'a_required_deactivated_variant_text',
                    ],
                    'tablet' => [
                        'sku',
                        'a_price',
                        'a_localizable_non_scopable_price',
                        'a_non_localizable_non_scopable_text',
                        'a_non_localizable_scopable_text',
                        'a_localizable_scopable_text',
                        'a_non_localizable_non_scopable_locale_specific',
                        'a_non_localizable_scopable_locale_specific',
                        'a_localizable_scopable_locale_specific',
                        'a_variation_axis',
                        'a_required_variant_text',
                        'a_required_deactivated_text',
                        'a_required_deactivated_variant_text',
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
                    'axes' => ['a_variation_axis'],
                    'attributes' => ['a_variation_axis', 'sku', 'a_required_variant_text', 'a_required_deactivated_variant_text'],
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModel('a_product_model', 'familyA_variant1');
        $attributesMask = $this->get(GetRequiredProductModelAttributesMaskQuery::class)->execute($productModelId);

        $ecommerceEnUsMask = $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $attributesMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $attributesMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'a_price-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-en_US',
            'a_non_localizable_scopable_text-ecommerce-<all_locales>',
            'a_localizable_non_scopable_locale_specific-<all_channels>-en_US'
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-en_US',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-en_US',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing( [
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-fr_FR',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-fr_FR',
            'a_non_localizable_non_scopable_locale_specific-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
            'a_localizable_scopable_locale_specific-tablet-fr_FR'
        ], $tabletFrFr->mask());

        $unknownProductId = new ProductId(42);
        $this->assertNull($this->get(GetRequiredProductModelAttributesMaskQuery::class)->execute($unknownProductId));
    }

    public function test_it_gives_the_non_required_attributes_masks_for_a_product_model_with_two_levels_of_variation()
    {
        $this->givenAttributes([
            ['code' => 'a_non_required_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_product_model_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_sub_product_model_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_product_variant_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_variation_axis_level_1', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
            ['code' => 'a_variation_axis_level_2', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_non_required_text',
                    'a_product_model_text',
                    'a_sub_product_model_text',
                    'a_product_variant_text',
                    'a_variation_axis_level_1',
                    'a_variation_axis_level_2',
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_product_model_text',
                        'a_sub_product_model_text',
                        'a_product_variant_text',
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
                    'attributes' => ['a_variation_axis_level_1', 'a_sub_product_model_text'],
                ],
                [
                    'level' => 2,
                    'axes' => ['a_variation_axis_level_2'],
                    'attributes' => ['a_variation_axis_level_2', 'sku', 'a_product_variant_text'],
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModel('a_root_product_model', 'familyA_variant2');
        $attributesMask = $this->get(GetRequiredProductModelAttributesMaskQuery::class)->execute($productModelId);

        $this->assertEqualsCanonicalizing([
            'a_product_model_text-ecommerce-en_US',
        ], $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US')->mask());

        $subProductModelId = $this->givenASubProductModel('a_sub_product_model', 'familyA_variant2', 'a_root_product_model');
        $attributesMask = $this->get(GetRequiredProductModelAttributesMaskQuery::class)->execute($subProductModelId);

        $this->assertEqualsCanonicalizing([
            'a_product_model_text-ecommerce-en_US',
            'a_variation_axis_level_1-<all_channels>-<all_locales>',
            'a_sub_product_model_text-<all_channels>-<all_locales>',
        ], $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US')->mask());
    }
}
