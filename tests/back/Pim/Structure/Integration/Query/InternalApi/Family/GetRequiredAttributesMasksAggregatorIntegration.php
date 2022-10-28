<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Pim\Structure\Integration\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\NonExistingFamiliesException;
use Webmozart\Assert\Assert;

final class GetRequiredAttributesMasksAggregatorIntegration extends AbstractGetRequiredAttributesMasksIntegration
{
    private GetRequiredAttributesMasks $getRequiredAttributesMasks;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getRequiredAttributesMasks = $this->get('akeneo.pim.structure.query.sql_get_required_attributes_masks');

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
                'attribute_codes' => ['sku'],
            ]
        ]);
    }

    public function test_that_the_generated_masks_are_ok(): void
    {
        $result = $this->getRequiredAttributesMasks->fromFamilyCodes(['familyA']);
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
        $result = $this->getRequiredAttributesMasks->fromFamilyCodes(['familyB', 'familyC']);
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

        $this->getRequiredAttributesMasks->fromFamilyCodes(['familyA', 'familyZ', 'familyY']);
    }
}
