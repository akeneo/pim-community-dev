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

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family\PriceCollectionSqlGetRequiredAttributesMasks;
use PHPUnit\Framework\Assert;

final class PriceCollectionSqlGetRequiredAttributesMasksIntegration extends AbstractGetRequiredAttributesMasksIntegration
{
    private PriceCollectionSqlGetRequiredAttributesMasks $priceCollectionSqlGetRequiredAttributesMasks;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->priceCollectionSqlGetRequiredAttributesMasks = $this->get(PriceCollectionSqlGetRequiredAttributesMasks::class);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => ['sku', 'a_price', 'a_localizable_non_scopable_price'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_price',
                    ],
                    'tablet' => [
                        'sku',
                        'a_price',
                        'a_localizable_non_scopable_price',
                    ],
                ],
            ],
            [
                'code' => 'familyB',
                'attribute_codes' => ['sku', 'a_price', 'a_localizable_non_scopable_price'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                    ],
                ],
            ],
            [
                'code' => 'familyC',
                'attribute_codes' => [],
            ],
        ]);
    }

    public function test_that_the_generated_masks_are_ok(): void
    {
        $result = $this->priceCollectionSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::assertCount(3, $familyAMask->masks());

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'a_price-USD-<all_channels>-<all_locales>',
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-en_US',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing( [
            'a_price-EUR-USD-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_price-EUR-USD-<all_channels>-fr_FR',
        ], $tabletFrFr->mask());
    }

    public function test_the_generated_mask_is_ok_for_a_family_without_requirement()
    {
        $result = $this->priceCollectionSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyB', 'familyC']);
        Assert::assertArrayNotHasKey('familyB', $result);
        Assert::assertArrayNotHasKey('familyC', $result);
    }
}
