<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\Family\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\NonExistingFamiliesException;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetRequiredAttributesMasksIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Note that ecommerce already exists by default
        $this->givenCurrencyForChannel([['code' => 'ecommerce', 'currencies' => ['USD']]]);

        $this->givenChannels([['code' => 'tablet', 'locales' => ['en_US', 'fr_FR'], 'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'], 'currencies' => ['USD', 'EUR']]]);

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
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => ['sku', 'a_price', 'a_localizable_non_scopable_price', 'a_non_required_text', 'a_non_localizable_non_scopable_text', 'a_localizable_non_scopable_text', 'a_non_localizable_scopable_text', 'a_localizable_scopable_text', 'a_non_localizable_non_scopable_locale_specific', 'a_localizable_non_scopable_locale_specific', 'a_non_localizable_scopable_locale_specific', 'a_localizable_scopable_locale_specific'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_price',
                        'a_localizable_non_scopable_text',
                        'a_non_localizable_scopable_text',
                        'a_localizable_non_scopable_locale_specific'
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
                        'a_localizable_scopable_locale_specific'
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
    }

    public function test_that_the_generated_masks_are_ok()
    {
        $result = $this->getRequiredAttributesMasks()->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::count($familyAMask->masks(), 3);

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

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

    public function test_the_generated_mask_is_ok_for_a_family_without_requirement()
    {
        $result = $this->getRequiredAttributesMasks()->fromFamilyCodes(['familyB', 'familyC']);
        Assert::count($result['familyB']->masks(), 3);
        foreach ($result['familyB']->masks() as $maskPerChannelAndLocale) {
            $this->assertEqualsCanonicalizing(['sku-<all_channels>-<all_locales>'], $maskPerChannelAndLocale->mask());
        }
        Assert::count($result['familyC']->masks(), 3);
        foreach ($result['familyC']->masks() as $maskPerChannelAndLocale) {
            $this->assertEqualsCanonicalizing(['sku-<all_channels>-<all_locales>'], $maskPerChannelAndLocale->mask());
        }
    }

    public function test_it_throws_an_exception_for_non_existing_families()
    {
        $this->expectException(NonExistingFamiliesException::class);
        $this->expectExceptionMessage("The following family codes do not exist: familyZ, familyY");

        $this->getRequiredAttributesMasks()->fromFamilyCodes(['familyA', 'familyZ', 'familyY']);
    }

    private function getRequiredAttributesMasks(): GetRequiredAttributesMasks
    {
        return $this->get('akeneo.pim.structure.query.sql_get_required_attributes_masks');
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
            $channel = $this->get('pim_catalog.repository.channel')->findOneBy(['code' => $channelData['code']]);;
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
