<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetNonRequiredAttributesMasksQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class GetNonRequiredAttributesMasksQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Note that ecommerce already exists by default (with only the activated locale en_US)
        $this->givenCurrencyForChannel([['code' => 'ecommerce', 'currencies' => ['USD']]]);

        $this->givenChannels([['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]]);

        $this->givenAttributes([
            ['code' => 'a_required_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_required_text_for_ecommerce_only', 'type' => AttributeTypes::TEXT, 'scopable' => true],
            // A price because the handling is different than other attribute
            ['code' => 'a_price', 'type' => AttributeTypes::PRICE_COLLECTION],
            ['code' => 'a_localizable_non_scopable_price', 'type' => AttributeTypes::PRICE_COLLECTION, 'localizable' => true],
            // Localizable and Scopable attributes
            ['code' => 'a_non_localizable_non_scopable_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_localizable_non_scopable_text', 'type' => AttributeTypes::TEXT, 'localizable' => true],
            ['code' => 'a_non_localizable_scopable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true],
            ['code' => 'a_localizable_scopable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            // Locale specific attributes
            ['code' => 'a_non_localizable_non_scopable_locale_specific_fr', 'type' => AttributeTypes::TEXT, 'available_locales' => ['fr_FR']],
            ['code' => 'a_localizable_non_scopable_locale_specific_us', 'type' => AttributeTypes::TEXT, 'localizable' => true, 'available_locales' => ['en_US']],
            ['code' => 'a_non_localizable_scopable_locale_specific_fr_us', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'available_locales' => ['fr_FR', 'en_US']],
            ['code' => 'a_localizable_scopable_locale_specific_fr', 'type' => AttributeTypes::TEXT, 'localizable' => true, 'scopable' => true, 'available_locales' => ['fr_FR']],
            ['code' => 'a_required_locale_specific_fr', 'type' => AttributeTypes::TEXT, 'available_locales' => ['fr_FR']],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_price',
                    'a_localizable_non_scopable_price',
                    'a_required_text',
                    'a_required_text_for_ecommerce_only',
                    'a_non_localizable_non_scopable_text',
                    'a_localizable_non_scopable_text',
                    'a_non_localizable_scopable_text',
                    'a_localizable_scopable_text',
                    'a_non_localizable_non_scopable_locale_specific_fr',
                    'a_localizable_non_scopable_locale_specific_us',
                    'a_non_localizable_scopable_locale_specific_fr_us',
                    'a_localizable_scopable_locale_specific_fr',
                    'a_required_locale_specific_fr'
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_required_text',
                        'a_required_text_for_ecommerce_only',
                    ],
                    'tablet' => [
                        'sku',
                        'a_required_text',
                        'a_required_locale_specific_fr',
                    ],
                ]
            ],
            [
                'code' => 'familyB',
                'attribute_codes' => ['sku', 'a_required_text'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_required_text',
                    ],
                    'tablet' => [
                        'sku',
                        'a_required_text',
                    ],
                ]
            ],
            [
                'code' => 'familyC',
                'attribute_codes' =>  ['sku', 'a_price'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                    ],
                ]
            ]
        ]);
    }

    public function test_it_gives_the_non_required_attributes_masks_for_a_family_with_requirements()
    {
        $result = $this->getNonRequiredAttributeMasksQuery()->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::count($familyAMask->masks(), 3);

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'a_price-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-USD-<all_channels>-en_US',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-en_US',
            'a_localizable_scopable_text-ecommerce-en_US',
            'a_non_localizable_scopable_text-ecommerce-<all_locales>',
            'a_localizable_non_scopable_locale_specific_us-<all_channels>-en_US',
            'a_non_localizable_scopable_locale_specific_fr_us-ecommerce-<all_locales>',
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'a_required_text_for_ecommerce_only-tablet-<all_locales>',
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-en_US',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-en_US',
            'a_localizable_scopable_text-tablet-en_US',
            'a_localizable_non_scopable_locale_specific_us-<all_channels>-en_US',
            'a_non_localizable_scopable_locale_specific_fr_us-tablet-<all_locales>',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing([
            'a_required_text_for_ecommerce_only-tablet-<all_locales>',
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-fr_FR',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-fr_FR',
            'a_localizable_scopable_text-tablet-fr_FR',
            'a_non_localizable_non_scopable_locale_specific_fr-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_locale_specific_fr_us-tablet-<all_locales>',
            'a_localizable_scopable_locale_specific_fr-tablet-fr_FR'
        ], $tabletFrFr->mask());

        $result = $this->getNonRequiredAttributeMasksQuery()->fromFamilyCodes(['familyB']);
        $familyBMask = $result['familyB'];
        Assert::count($familyBMask->masks(), 0);

        $result = $this->getNonRequiredAttributeMasksQuery()->fromFamilyCodes(['familyC']);
        $familyCMask = $result['familyC'];
        Assert::count($familyCMask->masks(), 3);

        $ecommerceEnUsMask = $familyCMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');

        $this->assertEqualsCanonicalizing([
            'a_price-USD-<all_channels>-<all_locales>',
        ], $ecommerceEnUsMask->mask());
    }

    private function getNonRequiredAttributeMasksQuery(): GetNonRequiredAttributesMasksQuery
    {
        return $this->get(GetNonRequiredAttributesMasksQuery::class);
    }

    private function givenAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeData['code'],
                    'type' => $attributeData['type'],
                    'localizable' => $attributeData['localizable'] ?? false,
                    'scopable' => $attributeData['scopable'] ?? false,
                    'group' => 'other',
                    'available_locales' => $attributeData['available_locales'] ?? [],
                    'decimals_allowed' => $attributeData['type'] === AttributeTypes::PRICE_COLLECTION ? false : null,
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenCurrencyForChannel(array $channelsData): void
    {
        $channels = array_map(function ($channelData) {
            $channel = $this->get('pim_catalog.repository.channel')->findOneBy(['code' => $channelData['code']]);
            $this->get('pim_catalog.updater.channel')->update(
                $channel,
                [
                    'currencies' => $channelData['currencies']
                ]
            );

            $errors = $this->get('validator')->validate($channel);
            Assert::count($errors, 0);

            return $channel;
        }, $channelsData);

        $this->get('pim_catalog.saver.channel')->saveAll($channels);
    }

    private function givenChannels(array $channelsData): void
    {
        $channels = array_map(function ($channelData) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
            $this->get('pim_catalog.updater.channel')->update(
                $channel,
                [
                    'code' => $channelData['code'],
                    'locales' => $channelData['locales'],
                    'currencies' => $channelData['currencies'],
                    'category_tree' => 'master'
                ]
            );

            $errors = $this->get('validator')->validate($channel);
            Assert::count($errors, 0);

            return $channel;
        }, $channelsData);

        $this->get('pim_catalog.saver.channel')->saveAll($channels);
    }

    private function givenFamilies(array $familiesData): void
    {
        $families = array_map(function ($familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update(
                $family,
                [
                    'code' => $familyData['code'],
                    'attributes'  =>  $familyData['attribute_codes'] ?? [],
                    'attribute_requirements' => $familyData['attribute_requirements'] ?? [],
                ]
            );

            $errors = $this->get('validator')->validate($family);
            Assert::count($errors, 0);

            return $family;
        }, $familiesData);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }
}
