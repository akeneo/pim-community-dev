<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetRequiredAttributesMasksQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRequiredAttributesMasksQueryIntegration extends CompletenessTestCase
{
    public function test_it_gives_the_required_attributes_masks_for_a_list_of_families()
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
            // Attribute required but deactivated from its attribute group
            ['code' => 'a_required_deactivated_text', 'type' => AttributeTypes::TEXT, 'group' => 'erp'],
        ]);

        $this->givenFamilies([
            [
                // PIM-9542: The code is numeric in order to ensure that using the array_key_fill do not affect the final result
                'code' => '1234',
                'attribute_codes' => ['sku', 'a_price', 'a_localizable_non_scopable_price', 'a_non_required_text', 'a_non_localizable_non_scopable_text', 'a_localizable_non_scopable_text', 'a_non_localizable_scopable_text', 'a_localizable_scopable_text', 'a_non_localizable_non_scopable_locale_specific', 'a_localizable_non_scopable_locale_specific', 'a_non_localizable_scopable_locale_specific', 'a_localizable_scopable_locale_specific', 'a_required_deactivated_text'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_price',
                        'a_localizable_non_scopable_text',
                        'a_non_localizable_scopable_text',
                        'a_localizable_non_scopable_locale_specific',
                        'a_required_deactivated_text',
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
                        'a_required_deactivated_text',
                    ],
                ]
            ],
            [
                'code' => 'familyB',
                'attribute_codes' => ['sku', 'a_non_required_text'],
            ],
            [
                'code' => 'familyC',
                'attribute_codes' => [],
            ]
        ]);

        $result = $this->get(GetRequiredAttributesMasksQuery::class)->fromFamilyCodes(['1234']);
        $family1234Mask = $result['1234'];
        $this->assertCount(3, $family1234Mask->masks());

        $ecommerceEnUsMask = $family1234Mask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $family1234Mask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $family1234Mask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            'a_price-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-en_US',
            'a_non_localizable_scopable_text-ecommerce-<all_locales>',
            'a_localizable_non_scopable_locale_specific-<all_channels>-en_US'
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-en_US',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-en_US',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing( [
            'sku-<all_channels>-<all_locales>',
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-fr_FR',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-fr_FR',
            'a_non_localizable_non_scopable_locale_specific-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
            'a_localizable_scopable_locale_specific-tablet-fr_FR'
        ], $tabletFrFr->mask());
    }
}
