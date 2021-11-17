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

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class TableValueCompletenessIntegration extends TestCase
{
    use EntityBuilderTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannel([
            'code' => 'mobile',
            'locales' => ['en_US', 'fr_FR'],
            'labels' => ['en_US' => 'Mobile', 'fr_FR' => 'Mobile'],
            'currencies' => ['USD']
        ]);

        $this->createChannel([
            'code' => 'tablet',
            'locales' => ['en_US', 'fr_FR'],
            'labels' => ['en_US' => 'tablet', 'fr_FR' => 'tablet'],
            'currencies' => ['USD']
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
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    public function test_it_computes_the_completeness_when_first_column_is_required(): void
    {
        $this->createFamily([
            'code' => 'familyA',
            'attributes' => ['sku', 'localizable_scopable_nutrition'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'localizable_scopable_nutrition'],
                'mobile' => ['sku', 'localizable_scopable_nutrition'],
                'tablet' => ['sku'],
            ],
        ]);

        $this->createProduct('test1', [
            'categories' => ['master'],
            'family' => 'familyA',
            'values' => [
                'localizable_scopable_nutrition' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => [
                            ['ingredient' => 'salt'],
                            ['ingredient' => 'egg', 'quantity' => 2],
                        ],
                    ],
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => [
                            ['ingredient' => 'salt'],
                            ['ingredient' => 'egg', 'quantity' => 2],
                        ],
                    ],
                ],
            ],
        ]);

        $completenesses = $this->get('pim_catalog.completeness.calculator')->fromProductIdentifiers(['test1']);
        $completenessForProduct = $completenesses['test1'] ?? null;
        Assert::assertInstanceOf(ProductCompletenessWithMissingAttributeCodesCollection::class, $completenessForProduct);

        Assert::assertEquals(2, $completenessForProduct->getCompletenessForChannelAndLocale('ecommerce', 'en_US')->requiredCount());
        Assert::assertEquals(100, $completenessForProduct->getCompletenessForChannelAndLocale('ecommerce', 'en_US')->ratio());

        Assert::assertEquals(2, $completenessForProduct->getCompletenessForChannelAndLocale('mobile', 'en_US')->requiredCount());
        Assert::assertEquals(100, $completenessForProduct->getCompletenessForChannelAndLocale('mobile', 'en_US')->ratio());
        Assert::assertEquals(2, $completenessForProduct->getCompletenessForChannelAndLocale('mobile', 'fr_FR')->requiredCount());
        Assert::assertEquals(50, $completenessForProduct->getCompletenessForChannelAndLocale('mobile', 'fr_FR')->ratio());

        Assert::assertEquals(1, $completenessForProduct->getCompletenessForChannelAndLocale('tablet', 'en_US')->requiredCount());
        Assert::assertEquals(100, $completenessForProduct->getCompletenessForChannelAndLocale('tablet', 'en_US')->ratio());
        Assert::assertEquals(1, $completenessForProduct->getCompletenessForChannelAndLocale('tablet', 'fr_FR')->requiredCount());
        Assert::assertEquals(100, $completenessForProduct->getCompletenessForChannelAndLocale('tablet', 'fr_FR')->ratio());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
