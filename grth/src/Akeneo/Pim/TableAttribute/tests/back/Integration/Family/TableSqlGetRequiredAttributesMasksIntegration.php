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

namespace Akeneo\Test\Pim\TableAttribute\Integration\Family;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Infrastructure\Family\Sql\TableSqlGetRequiredAttributesMasks;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class TableSqlGetRequiredAttributesMasksIntegration extends TestCase
{
    private TableSqlGetRequiredAttributesMasks $tableSqlGetRequiredAttributesMasks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tableSqlGetRequiredAttributesMasks = $this->get(TableSqlGetRequiredAttributesMasks::class);

        $this->createChannel([
            'code' => 'tablet',
            'locales' => ['en_US', 'fr_FR'],
            'labels' => ['en_US' => 'tablet', 'fr_FR' => 'Tablette'],
            'currencies' => ['USD'],
        ]);

        $this->createAttribute([
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);

        $this->createAttribute([
            'code' => 'localizable_scopable_nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => true,
            'scopable' => true,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);

        $this->createFamily([
            'code' => 'familyA',
            'attribute_codes' => ['sku', 'nutrition', 'localizable_scopable_nutrition'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'nutrition', 'localizable_scopable_nutrition'],
                'tablet' => ['sku', 'nutrition'],
            ],
        ]);
        $this->createFamily([
            'code' => 'familyB',
            'attribute_codes' => ['sku', 'nutrition', 'localizable_scopable_nutrition'],
        ]);
        $this->createFamily([
            'code' => 'familyC',
            'attribute_codes' => ['sku'],
        ]);
    }

    public function test_it_returns_the_required_attributes_masks_when_no_columns_are_required_by_the_user(): void
    {
        $result = $this->tableSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA']);
        $familyAMask = $result['familyA'];
        Assert::assertCount(3, $familyAMask->masks());

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
            \sprintf('localizable_scopable_nutrition-%s-ecommerce-en_US', $this->getColumnId('localizable_scopable_nutrition', 'ingredient')),
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
        ], $tabletFrFr->mask());
    }

    public function test_it_returns_empty_mask_for_non_required_attributes(): void
    {
        $result = $this->tableSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyB', 'familyC']);
        Assert::assertArrayNotHasKey('familyB', $result);
        Assert::assertArrayNotHasKey('familyC', $result);
    }

    public function test_it_returns_the_required_attributes_masks_when_several_columns_are_required_by_the_user(): void
    {
        $this->createAttribute([
            'code' => 'lsnwr', // localizable_scopable_nutrition_with_required
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => true,
            'scopable' => true,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                    'is_required_for_completeness' => true,
                ],
                [
                    'code' => 'is_allergenic',
                    'data_type' => 'number',
                    'labels' => [],
                    'is_required_for_completeness' => false,
                ],
                [
                    'code' => 'comment',
                    'data_type' => 'number',
                    'labels' => [],
                    'is_required_for_completeness' => true,
                ],
            ],
        ]);

        $this->createFamily([
            'code' => 'familyD',
            'attribute_codes' => ['sku', 'nutrition', 'lsnwr'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'nutrition', 'lsnwr'],
            ],
        ]);

        $result = $this->tableSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyD']);
        $familyAMask = $result['familyD'];
        Assert::assertCount(1, $familyAMask->masks());

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');

        $commentId = $this->getColumnId('lsnwr', 'comment');
        $ingredientId = $this->getColumnId('lsnwr', 'ingredient');
        $quantityId = $this->getColumnId('lsnwr', 'quantity');
        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
            \sprintf('lsnwr-%s-ecommerce-en_US', $commentId . '-' . $ingredientId . '-' . $quantityId),
        ], $ecommerceEnUsMask->mask());
    }

    public function test_it_returns_the_required_attributes_masks_for_several_families(): void
    {
        $this->createFamily([
            'code' => 'familyD',
            'attribute_codes' => ['sku', 'nutrition'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'nutrition'],
            ],
        ]);

        $result = $this->tableSqlGetRequiredAttributesMasks->fromFamilyCodes(['familyA', 'familyD']);
        $familyAMask = $result['familyA'];
        Assert::assertCount(3, $familyAMask->masks());

        $ecommerceEnUsMask = $familyAMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');
        $tabletEnUS = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'en_US');
        $tabletFrFr = $familyAMask->requiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR');

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
            \sprintf(
                'localizable_scopable_nutrition-%s-ecommerce-en_US',
                $this->getColumnId('localizable_scopable_nutrition', 'ingredient')
            ),
        ], $ecommerceEnUsMask->mask());

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
        ], $tabletEnUS->mask());

        $this->assertEqualsCanonicalizing([
            \sprintf('nutrition-%s-<all_channels>-<all_locales>', $this->getColumnId('nutrition', 'ingredient')),
        ], $tabletFrFr->mask());
    }

    private function createAttribute(array $values): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $values);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $familyData): void
    {
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
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * {@inheritDoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createChannel(array $channelData): void
    {
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
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    private function getColumnId(string $attributeCode, string $columnCode): string
    {
        $query = <<<SQL
SELECT c.id
FROM pim_catalog_table_column c
    JOIN pim_catalog_attribute a ON a.id = c.attribute_id
WHERE a.code = :attribute_code AND c.code = :column_code
SQL;
        return $this->get('database_connection')->executeQuery($query, [
            'attribute_code' => $attributeCode,
            'column_code' => $columnCode,
        ])->fetchOne();
    }
}
