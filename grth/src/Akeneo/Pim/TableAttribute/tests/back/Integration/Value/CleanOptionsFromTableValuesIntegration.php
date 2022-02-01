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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class CleanOptionsFromTableValuesIntegration extends TestCase
{
    private JobLauncher $jobLauncher;
    private Connection $connection;

    /** @test */
    public function cleanTableValuesOnProductAndProductModelAfterRemovingAnOption(): void
    {
        $this->givenOptionsAreRemovedFromTableConfiguration();
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertJobSuccessful();

        $this->assertProductRawValues('test1', 'nutrition', [['ingredient' => 'salt', 'is_allergenic' => false]]);
        $this->assertProductRawValues('test2', 'nutrition', null);
        $this->assertProductModelRawValues('pm', 'nutrition', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->connection = $this->get('database_connection');

        $options = \array_map(
            fn (int $num): array => ['code' => \sprintf('option_%d', $num)],
            \range(1, 19997)
        );

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
                        'options' => \array_merge(
                            [
                                ['code' => 'salt'],
                                ['code' => 'egg'],
                                ['code' => 'butter'],
                            ],
                            $options
                        ),
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
            ['ingredient' => 'option_19000', 'quantity' => 3],
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

    private function givenOptionsAreRemovedFromTableConfiguration(): void
    {
        $newOptions = [
            ['code' => 'salt'],
            ['code' => 'butter'],
        ];

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
        self::assertCount(0, $constraints, (string)$constraints);
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
        self::assertCount(0, $constraints, (string)$constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints, (string)$constraints);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    private function assertJobSuccessful()
    {
        $res = $this->connection->executeQuery(
            <<<SQL
            SELECT execution.status = 1 AS success
            FROM akeneo_batch_job_execution execution
            INNER JOIN akeneo_batch_job_instance instance ON execution.job_instance_id = instance.id
            WHERE instance.code = 'clean_table_values_following_deleted_options'
            ORDER BY execution.id DESC LIMIT 1
        SQL
        )->fetchOne();

        Assert::assertTrue((bool)$res, 'The cleaning job was not successful');
    }

    private function assertProductRawValues(string $identifier, string $attributeCode, ?array $expectedValues): void
    {
        $actualRawValues = $this->connection->executeQuery(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchOne();

        Assert::assertNotFalse($actualRawValues);

        $this->assertRawValues(
            \json_decode($actualRawValues, true)[$attributeCode]['<all_channels>']['<all_locales>'] ?? null,
            $expectedValues
        );
    }

    private function assertProductModelRawValues(string $code, string $attributeCode, ?array $expectedValues): void
    {
        $actualRawValues = $this->connection->executeQuery(
            'SELECT raw_values FROM pim_catalog_product_model WHERE code = :code',
            ['code' => $code]
        )->fetchOne();

        Assert::assertNotFalse($actualRawValues);

        $this->assertRawValues(
            \json_decode($actualRawValues, true)[$attributeCode]['<all_channels>']['<all_locales>'] ?? null,
            $expectedValues
        );
    }

    private function assertRawValues(?array $actualRawValues, ?array $expectedValues): void
    {
        if (null === $expectedValues) {
            Assert::assertNull($actualRawValues);

            return;
        }

        Assert::assertIsArray($actualRawValues);
        Assert::assertSame(
            \count($expectedValues),
            \count($actualRawValues),
            'The raw values do not have the expected number of rows'
        );

        foreach ($actualRawValues as $index => $actualRow) {
            Assert::assertSame(
                \count($actualRow),
                \count($expectedValues[$index]),
                \sprintf('The row with index %d does not have the expected number of cells', $index)
            );
            foreach ($actualRow as $columnId => $actualValue) {
                $code = ColumnId::fromString($columnId)->extractColumnCode()->asString();
                Assert::assertArrayHasKey($code, $expectedValues[$index]);
                Assert::assertSame($expectedValues[$index][$code], $actualValue);
            }
        }
    }
}
