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

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family\DefaultSqlGetRequiredAttributesMasks;
use Webmozart\Assert\Assert;

final class DefaultSqlGetRequiredAttributesMasksIntegration extends AbstractGetRequiredAttributesMasksIntegration
{
    private DefaultSqlGetRequiredAttributesMasks $defaultSqlGetRequiredAttributesMasks;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultSqlGetRequiredAttributesMasks = $this->get(DefaultSqlGetRequiredAttributesMasks::class);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => ['sku', 'a_non_required_text', 'a_non_localizable_non_scopable_text', 'a_localizable_non_scopable_text', 'a_non_localizable_scopable_text', 'a_localizable_scopable_text', 'a_non_localizable_non_scopable_locale_specific', 'a_localizable_non_scopable_locale_specific', 'a_non_localizable_scopable_locale_specific', 'a_localizable_scopable_locale_specific'],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_localizable_non_scopable_text',
                        'a_non_localizable_scopable_text',
                        'a_localizable_non_scopable_locale_specific'
                    ],
                    'tablet' => [
                        'sku',
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
                'attribute_requirements' => [
                    'ecommerce' => ['sku'],
                    'tablet' => ['sku'],
                ],
            ],
            [
                'code' => 'familyC',
                'attribute_codes' => ['sku'],
            ],
        ]);
    }

    public function test_that_the_generated_masks_are_ok(): void
    {
        $result = $this->defaultSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::count($familyAMask->masks(), 3);

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            'a_localizable_non_scopable_text-<all_channels>-en_US',
            'a_non_localizable_scopable_text-ecommerce-<all_locales>',
            'a_localizable_non_scopable_locale_specific-<all_channels>-en_US'
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-en_US',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            'a_non_localizable_non_scopable_text-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_text-tablet-<all_locales>',
            'a_localizable_scopable_text-tablet-fr_FR',
            'a_non_localizable_non_scopable_locale_specific-<all_channels>-<all_locales>',
            'a_non_localizable_scopable_locale_specific-tablet-<all_locales>',
            'a_localizable_scopable_locale_specific-tablet-fr_FR'
        ], $tabletFrFr->mask());
    }

    public function test_that_the_generated_masks_are_empty_if_there_is_no_attribute_requirement(): void
    {
        $this->get('database_connection')->executeStatement(
            'DELETE FROM pim_catalog_attribute_requirement;'
        );

        $result = $this->defaultSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $this->assertEquals([], $result);
    }

    public function test_that_the_generated_masks_are_empty_if_attribute_requirements_are_no_required(): void
    {
        $this->get('database_connection')->executeStatement(
            'UPDATE pim_catalog_attribute_requirement SET required = 0;'
        );

        $result = $this->defaultSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $this->assertEquals([], $result);
    }

    public function test_that_the_generated_masks_are_ok_if_only_one_attribute_is_required(): void
    {
        $this->get('database_connection')->executeStatement(
            'UPDATE pim_catalog_attribute_requirement SET required = 0;'
        );

        $requirementId = $this->get('database_connection')->fetchOne(
            'SELECT car.id FROM pim_catalog_attribute_requirement car LEFT JOIN pim_catalog_channel cc ON car.channel_id = cc.id LEFT JOIN pim_catalog_family cf ON car.family_id = cf.id WHERE cc.code = "ecommerce" AND cf.code = "familyA" LIMIT 1;'
        );

        $sql = "UPDATE pim_catalog_attribute_requirement SET required = 1 WHERE id = :test_result";

        $params['test_result'] = $requirementId;

        $this->get('database_connection')->executeStatement($sql, $params);

        $result = $this->defaultSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::count($familyAMask->masks(), 1);

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>'
        ], $ecommerceEnUsMask->mask());
    }

    public function test_the_generated_mask_is_ok_for_a_family_without_requirement()
    {
        $result = $this->defaultSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyB', 'familyC', 'familyD']);
        Assert::count($result['familyB']->masks(), 3);
        foreach ($result['familyB']->masks() as $maskPerChannelAndLocale) {
            $this->assertEqualsCanonicalizing(['sku-<all_channels>-<all_locales>'], $maskPerChannelAndLocale->mask());
        }
        Assert::count($result['familyC']->masks(), 3);
        foreach ($result['familyC']->masks() as $maskPerChannelAndLocale) {
            $this->assertEqualsCanonicalizing(['sku-<all_channels>-<all_locales>'], $maskPerChannelAndLocale->mask());
        }
    }
}
