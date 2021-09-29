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

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver\TableConfigurationSaver;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class NonExistentTableValueFilterIntegration extends TestCase
{
    /** @test */
    public function it_filters_non_existent_table_values(): void
    {
        /** @var Product $product */
        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'nutrition' => [
                ['locale' => null, 'scope' => null, 'data' => [
                    ['INGredient' => 'SALT', 'quantity' => 10, 'description' => 'test'],
                    ['INGredient' => 'egg', 'quantity' => 5],
                    ['ingredient' => 'sugar', 'quantity' => 6, 'supplier' => 'akeneo'],
                    ['ingredient' => 'pepper', 'supplier' => 'other'],
                ]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('nutrition');
        // Table configuration changes:
        //   - remove "egg" option in "ingredient" column
        //   - remove "other" option in "supplier" column
        //   - remove "description" column
        $attribute->setRawTableConfiguration([
            [
                'code' => 'ingredient',
                'data_type' => 'select',
                'options' => [
                    ['code' => 'salt'],
                    ['code' => 'sugar'],
                    ['code' => 'pepper'],
                ],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'number',
            ],
            [
                'code' => 'supplier',
                'data_type' => 'select',
                'options' => [['code' => 'akeneo']],
            ],
        ]);
        $this->get(TableConfigurationSaver::class)->save($attribute);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get(TableConfigurationRepository::class)->clearCache();
        /** @var Product $product */
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        self::assertNotNull($product, 'The product is not found');
        $value = $product->getValue('nutrition');
        self::assertInstanceOf(TableValue::class, $value);
        self::assertEqualsCanonicalizing(
            [
                ['INGredient' => 'salt', 'quantity' => 10],
                ['ingredient' => 'sugar', 'quantity' => 6, 'supplier' => 'akeneo'],
                ['ingredient' => 'pepper'],
            ],
            $value->getData()->normalize()
        );
    }

    /** @test */
    public function it_filters_the_values_when_all_columns_are_replaced(): void
    {
        /** @var Product $product */
        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'nutrition' => [
                ['locale' => null, 'scope' => null, 'data' => [
                    ['INGredient' => 'SALT', 'quantity' => 10, 'description' => 'test'],
                    ['INGredient' => 'egg', 'quantity' => 5],
                ]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('nutrition');
        $attribute->setRawTableConfiguration([
            [
                'code' => 'ingredient',
                'data_type' => 'select',
                'options' => [
                    ['code' => 'salt_new'],
                    ['code' => 'sugar_new'],
                    ['code' => 'pepper_new'],
                ],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'number',
            ],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get(TableConfigurationSaver::class)->save($attribute);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get(TableConfigurationRepository::class)->clearCache();
        /** @var Product $product */
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        self::assertNotNull($product, 'The product is not found');
        $value = $product->getValue('nutrition');
        self::assertNull($value);
    }

    /** @test */
    public function it_filters_the_values_when_the_attribute_is_removed(): void
    {
        /** @var Product $product */
        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'nutrition' => [
                ['locale' => null, 'scope' => null, 'data' => [
                    ['INGredient' => 'SALT', 'quantity' => 10, 'description' => 'test'],
                    ['INGredient' => 'egg', 'quantity' => 5],
                    ['ingredient' => 'sugar', 'quantity' => 6, 'supplier' => 'akeneo'],
                    ['ingredient' => 'pepper', 'supplier' => 'other'],
                ]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('nutrition');
        $this->get('pim_catalog.remover.attribute')->remove($attribute);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get(TableConfigurationRepository::class)->clearCache();
        /** @var Product $product */
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        self::assertNotNull($product, 'The product is not found');
        $value = $product->getValue('nutrition');
        self::assertNull($value);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attribute = new Attribute();
        $attribute->setEntityType(Product::class);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'sugar'],
                        ['code' => 'pepper'],
                        ['code' => 'egg'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                ],
                [
                    'code' => 'supplier',
                    'data_type' => 'select',
                    'options' => [['code' => 'akeneo'], ['code' => 'other']],
                ],
                [
                    'code' => 'description',
                    'data_type' => 'text',
                ],
            ],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
