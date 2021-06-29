<?php


namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class GetAttributeTypesMasksQueryIntegration extends CompletenessTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Note that ecommerce already exists by default (with only the activated locale en_US)
        $this->givenCurrencyForChannel([
            ['code' => 'ecommerce', 'currencies' => ['USD']]
        ]);

        $this->givenChannels([
            ['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'Tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]
        ]);

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
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_text',
                        'an_image',
                    ],
                    'tablet' => [
                        'sku',
                        'a_text',
                        'a_second_image',
                    ],
                ]
            ],
        ]);
    }

    public function test_it_retrieves_image_attributes()
    {
        $result = $this->get('akeneo.pim.automation.data_quality_insights.query.product.get_enrichment_images_masks')->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];

        $this->assertCount(3, $familyAMask->masks());

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'an_image-<all_channels>-<all_locales>',
            'a_second_image-ecommerce-<all_locales>',
            'a_third_image-<all_channels>-en_US',
            'an_us_image-<all_channels>-<all_locales>',
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'an_image-<all_channels>-<all_locales>',
            'a_second_image-tablet-<all_locales>',
            'a_third_image-<all_channels>-en_US',
            'an_us_image-<all_channels>-<all_locales>',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing( [
            'an_image-<all_channels>-<all_locales>',
            'a_second_image-tablet-<all_locales>',
            'a_third_image-<all_channels>-fr_FR',
            'a_fr_image-<all_channels>-<all_locales>',
        ], $tabletFrFr->mask());

    }
}
