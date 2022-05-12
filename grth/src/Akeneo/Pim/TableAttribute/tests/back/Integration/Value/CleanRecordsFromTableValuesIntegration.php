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

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class CleanRecordsFromTableValuesIntegration extends TestCase
{
    use EntityBuilderTrait;

    private JobLauncher $jobLauncher;
    private Connection $connection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();
        parent::setUp();

        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->connection = $this->get('database_connection');

        $this->createReferenceEntity('brand');
        $this->createRecord('brand', 'brand1', []);
        $this->createRecord('brand', 'brand2', []);

        $this->createAttribute(
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
                        'code' => 'brandCode',
                        'data_type' => 'reference_entity',
                        'labels' => [
                            'en_US' => 'Brand',
                        ],
                        'reference_entity_identifier' => 'brand',
                    ],
                ],
            ]
        );

        $this->createProduct('test1', 'nutrition', [
            ['ingredient' => 'salt', 'brandCode' => 'brand1'],
            ['ingredient' => 'egg', 'quantity' => 2, 'brandCode' => 'brand2'],
        ]);
        $this->createProduct('test2', 'nutrition', [
            ['ingredient' => 'egg', 'quantity' => 3, 'brandCode' => 'brand1'],
        ]);
    }

    /** @test */
    public function cleanTableValuesOnProductAfterRemovingARecord(): void
    {
        $this->deleteRecord('brand', 'brand1');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertJobSuccessful();

        $this->assertProductRawValues(
            'test1',
            'nutrition',
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2, 'brandCode' => 'brand2'],
            ]
        );
        $this->assertProductRawValues(
            'test2',
            'nutrition',
            [['ingredient' => 'egg', 'quantity' => 3]]
        );
    }

    /** @test */
    public function cleanTableValuesOnProductAfterRemovingSeveralRecords(): void
    {
        $this->deleteRecords('brand', ['brand1', 'brand2']);
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertJobSuccessful();

        $this->assertProductRawValues(
            'test1',
            'nutrition',
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );
        $this->assertProductRawValues(
            'test2',
            'nutrition',
            [['ingredient' => 'egg', 'quantity' => 3]]
        );
    }

    /** @test */
    public function cleanTableValuesOnProductWhenFirstColumnIsReferenceEntity(): void
    {
        $this->createAttribute(
            [
                'code' => 'nutrition2',
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'table_configuration' => [
                    [
                        'code' => 'brandCode',
                        'data_type' => 'reference_entity',
                        'labels' => [
                            'en_US' => 'Brand',
                        ],
                        'reference_entity_identifier' => 'brand',
                        'is_required_for_completeness' => true,
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'number',
                        'labels' => [
                            'en_US' => 'Quantity',
                        ],
                    ],
                ],
            ]
        );

        $this->createProduct('test3', 'nutrition2', [
            ['brandCode' => 'brand1', ],
            ['brandCode' => 'brand2', 'quantity' => 2],
        ]);

        $this->createProduct('test4', 'nutrition2', [
            ['brandCode' => 'brand1', 'quantity' => 9 ],
        ]);

        $this->deleteRecord('brand', 'brand1');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertJobSuccessful();

        $this->assertProductRawValues(
            'test3',
            'nutrition2',
            [
                ['quantity' => 2, 'brandCode' => 'brand2'],
            ]
        );

        $this->assertProductRawValues('test4', 'nutrition2', null);
    }

    /** @test */
    public function cleanTableValuesOnProductModel(): void
    {
        $this->createAttribute([
            'code' => 'size',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => 'other',
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
            ['ingredient' => 'egg', 'quantity' => 30, 'brandCode' => 'brand1'],
        ]);

        $this->deleteRecord('brand', 'brand1');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertJobSuccessful();

        $this->assertProductModelRawValues(
            'pm',
            'nutrition',
            [
                ['ingredient' => 'egg', 'quantity' => 30],
            ]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(string $identifier, string $tableAttributeCode, array $nutritionValue): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'categories' => ['master'],
                'values' => [
                    $tableAttributeCode => [
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

    private function createReferenceEntity(string $referenceEntityIdentifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($referenceEntityIdentifier, []);

        $violations = $this->get('validator')->validate($createCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        ($handler)($createCommand);
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
