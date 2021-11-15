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

namespace Akeneo\Pim\TableAttribute\tests\back\Integration\Value;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use PHPUnit\Framework\Assert;

final class CleanTableValuesIntegration extends TestCase
{
    private JobLauncher $jobLauncher;

    /** @test */
    public function cleanTableValuesOnProductAndProductModelAfterRemovingAnOption(): void
    {
        $this->givenOptionIsRemovedFromTableConfiguration(SelectOptionCode::fromString('egg'));
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $test1Product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test1');
        Assert::assertNotNull($test1Product);
        $value = $test1Product->getValue('nutrition');
        Assert::assertNotNull($value);
        Assert::assertEqualsCanonicalizing(
            [['ingredient' => 'salt', 'is_allergenic' => false]],
            $value->getData()->normalize()
        );

        $test2Product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test2');
        Assert::assertNotNull($test2Product);
        Assert::assertNull($test2Product->getValue('nutrition'));

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('pm');
        Assert::assertNotNull($productModel);
        Assert::assertNull($productModel->getValue('nutrition'));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => 'nutrition',
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'table_configuration' => [
                    [
                        'code' => 'ingredient',
                        'data_type' => 'select',
                        'labels' => [
                            'en_US' => 'Ingredients',
                        ],
                        'options' => [
                            ['code' => 'salt'],
                            ['code' => 'egg'],
                            ['code' => 'butter'],
                        ],
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'number',
                        'labels' => [
                            'en_US' => 'Quantity',
                        ],
                    ],
                    [
                        'code' => 'is_allergenic',
                        'data_type' => 'boolean',
                        'labels' => [
                            'en_US' => 'Is allergenic',
                        ],
                    ],
                ],
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->createProduct('test1', [
            ['ingredient' => 'salt', 'is_allergenic' => false],
            ['ingredient' => 'egg', 'quantity' => 2],
        ]);
        $this->createProduct('test2', [
            ['ingredient' => 'egg', 'quantity' => 3],
        ]);

        $this->createAttribute([
            'code' => 'size',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createAttributeOption(['attribute' => 'size', 'code' => 's']);

        $this->createFamily([
            'code' => 'shoes',
            'attributes' => ['sku', 'size', 'nutrition'],
            'attribute_requirements' => [],
        ]);

        $this->createFamilyVariant([
            'code' => 'shoe_size',
            'family' => 'shoes',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['size'],
                ],
            ],
        ]);

        $this->createProductModel('pm', [
            ['ingredient' => 'egg', 'quantity' => 30],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenOptionIsRemovedFromTableConfiguration(SelectOptionCode $optionCode): void
    {
        $newOptions = \array_filter(
            [
                ['code' => 'salt'],
                ['code' => 'egg'],
                ['code' => 'butter'],
            ],
            static fn (array $option): bool => $option['code'] !== $optionCode->asString()
        );

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('nutrition');
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => 'nutrition',
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'table_configuration' => [
                    [
                        'code' => 'ingredient',
                        'data_type' => 'select',
                        'labels' => [
                            'en_US' => 'Ingredients',
                        ],
                        'options' => $newOptions,
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'number',
                        'labels' => [
                            'en_US' => 'Quantity',
                        ],
                    ],
                    [
                        'code' => 'is_allergenic',
                        'data_type' => 'boolean',
                        'labels' => [
                            'en_US' => 'Is allergenic',
                        ],
                    ],
                ],
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createProduct(string $identifier, array $nutritionValue): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'categories' => ['master'],
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => $nutritionValue,
                        ],
                    ],
                ],
            ]
        );

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductModel(string $code, array $nutritionValue): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create($code);
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'categories' => ['master'],
                'family_variant' => 'shoe_size',
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => $nutritionValue,
                        ],
                    ],
                ],
            ]
        );

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createAttribute(array $data): void
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOption(array $data): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }
}
