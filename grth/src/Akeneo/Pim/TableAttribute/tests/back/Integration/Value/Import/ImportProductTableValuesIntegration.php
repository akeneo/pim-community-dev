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

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Import;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Type;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\Common\Creator\WriterFactory;
use PHPUnit\Framework\Assert;

final class ImportProductTableValuesIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_product_table_values_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_product_table_values_import';

    private JobLauncher $jobLauncher;
    private ProductRepositoryInterface $productRepository;
    private TableNormalizer $tableNormalizer;

    /** @test */
    public function it_imports_product_table_values_in_csv(): void
    {
        $csv = <<<CSV
product;attribute;ingredient;quantity;allergen;additional_info;nutrition_score;weight
111111;nutrition-en_US-ecommerce;salt;20;1;text;A;"100 KILOGRAM"
111111;nutrition-en_US-ecommerce;sugar;24;0;text2;B;
111111;nutrition-fr_FR-mobile;egg;12;;text3;C;"200 GRAM"
111111;nutrition-fr_FR-mobile;pepper;;1;text4;D;
111111;nutrition-en_US-mobile;pepper;;1;text5;D;
111112;nutrition-en_US-mobile;pepper;;;;;
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $product = $this->productRepository->findOneByIdentifier('111111');
        Assert::assertNotNull($product);
        Assert::assertCount(4, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'ecommerce');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            [
                'ingredient' => 'salt',
                'quantity' => '20',
                'allergen' => true,
                'additional_info' => 'text',
                'nutrition_score' => 'A',
                'weight' => [
                    'amount' => '100',
                    'unit' => 'KILOGRAM',
                ]
            ],
            $this->getLine($normalizedTable, 'salt')
        );
        Assert::assertEquals(
            ['ingredient' => 'sugar', 'quantity' => '24', 'allergen' => false, 'additional_info' => 'text2', 'nutrition_score' => 'B'],
            $this->getLine($normalizedTable, 'sugar')
        );
        Assert::assertEquals(
            ['ingredient' => 'egg', 'quantity' => 20, 'allergen' => true, 'nutrition_score' => 'C'],
            $this->getLine($normalizedTable, 'egg')
        );

        $value = $product->getValue('nutrition', 'fr_FR', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            [
                'ingredient' => 'egg',
                'quantity' => '12',
                'additional_info' => 'text3',
                'nutrition_score' => 'C',
                'weight' => [
                    'amount' => '200',
                    'unit' => 'GRAM',
                ]
            ],
            $this->getLine($normalizedTable, 'egg')
        );
        Assert::assertEquals(
            ['ingredient' => 'pepper', 'allergen' => true, 'additional_info' => 'text4', 'nutrition_score' => 'D'],
            $this->getLine($normalizedTable, 'pepper')
        );
        Assert::assertEquals(
            ['ingredient' => 'sugar', 'quantity' => 50, 'allergen' => false, 'additional_info' => 'this is a text', 'nutrition_score' => 'B'],
            $this->getLine($normalizedTable, 'sugar')
        );

        $value = $product->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper', 'allergen' => true, 'additional_info' => 'text5', 'nutrition_score' => 'D'],
            $this->getLine($normalizedTable, 'pepper')
        );

        $product = $this->productRepository->findOneByIdentifier('111112');
        Assert::assertNotNull($product);
        Assert::assertCount(2, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper'],
            $this->getLine($normalizedTable, 'pepper')
        );
    }

    /** @test */
    public function it_skips_invalid_rows(): void
    {
        $csv = <<<CSV
product;attribute;ingredient;quantity;allergen;additional_info;nutrition_score;weight
111111;nutrition-en_US-ecommerce;salt;20;1;text;A;" 100 GRAM"
111111;nutrition-en_US-ecommerce;sugar;24;0;text2;B;
111111;nutrition-fr_FR-mobile;egg;12;;text3;UNKNOWN;
111111;nutrition-fr_FR-mobile;pepper;;1;text4;D;
111111;nutrition-en_US-mobile;pepper;;1;text5;D;
111112;nutrition-en_US-mobile;pepper;;;;;
111111;nutrition-en_US-ecommerce;egg;30;0;text;A;"toto"
111111;nutrition-en_US-ecommerce;egg;40;0;text;A;"50 KILO GRAM"
111111;nutrition-en_US-ecommerce;egg;50;0;text;A;"200 UNKNOWN"
111111;nutrition-en_US-ecommerce;egg;60;0;text;A;"coucou UNKNOWN"
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $product = $this->productRepository->findOneByIdentifier('111111');
        Assert::assertNotNull($product);
        Assert::assertCount(4, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'ecommerce');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            [
                'ingredient' => 'salt',
                'quantity' => '20',
                'allergen' => true,
                'additional_info' => 'text',
                'nutrition_score' => 'A',
                'weight' => [
                    'amount' => '100',
                    'unit' => 'GRAM',
                ],
            ],
            $this->getLine($normalizedTable, 'salt')
        );
        Assert::assertEquals(
            ['ingredient' => 'sugar', 'quantity' => '24', 'allergen' => false, 'additional_info' => 'text2', 'nutrition_score' => 'B'],
            $this->getLine($normalizedTable, 'sugar')
        );

        // Egg is not modified because the weights are invalid and ignored
        Assert::assertEquals(
            ['ingredient' => 'egg', 'quantity' => 20, 'allergen' => true, 'nutrition_score' => 'C'],
            $this->getLine($normalizedTable, 'egg')
        );

        $value = $product->getValue('nutrition', 'fr_FR', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        // Row is not updated as the UNKNOWN nutrition score does not exist
        Assert::assertEquals(
            ['ingredient' => 'egg', 'quantity' => '23', 'allergen' => true, 'nutrition_score' => 'C'],
            $this->getLine($normalizedTable, 'egg')
        );
        Assert::assertEquals(
            ['ingredient' => 'pepper', 'allergen' => true, 'additional_info' => 'text4', 'nutrition_score' => 'D'],
            $this->getLine($normalizedTable, 'pepper')
        );
        Assert::assertEquals(
            ['ingredient' => 'sugar', 'quantity' => 50, 'allergen' => false, 'additional_info' => 'this is a text', 'nutrition_score' => 'B'],
            $this->getLine($normalizedTable, 'sugar')
        );

        $value = $product->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper', 'allergen' => true, 'additional_info' => 'text5', 'nutrition_score' => 'D'],
            $this->getLine($normalizedTable, 'pepper')
        );

        $product = $this->productRepository->findOneByIdentifier('111112');
        Assert::assertNotNull($product);
        Assert::assertCount(2, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper'],
            $this->getLine($normalizedTable, 'pepper')
        );

        $this->assertThereShouldBeAWarning('Make sure you only use existing option codes, current value: "UNKNOWN"');
    }

    /** @test */
    public function it_imports_table_attributes_from_an_xlsx_file(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_import');
        $writer = WriterFactory::createFromType(Type::XLSX);
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            \array_map(
                fn (array $data): Row => WriterEntityFactory::createRowFromArray($data),
                [
                    ['product', 'attribute', 'ingredient', 'quantity', 'allergen', 'additional_info', 'nutrition_score'],
                    ['111111', 'nutrition-en_US-ecommerce', 'salt', '20', '1', 'text', 'A'],
                    ['111112', 'nutrition-en_US-mobile', 'sugar', '12', '0', 'text2', 'C'],
                ]
            )
        );
        $writer->close();

        $this->jobLauncher->launchImport(
            self::XLSX_IMPORT_JOB_CODE,
            file_get_contents($temporaryFile),
            null,
            [],
            [],
            'xlsx'
        );

        $product = $this->productRepository->findOneByIdentifier('111111');
        Assert::assertNotNull($product);
        Assert::assertCount(3, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'ecommerce');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'salt', 'quantity' => '20', 'allergen' => true, 'additional_info' => 'text', 'nutrition_score' => 'A'],
            $this->getLine($normalizedTable, 'salt')
        );


        $product = $this->productRepository->findOneByIdentifier('111112');
        Assert::assertNotNull($product);
        Assert::assertCount(2, $product->getValues()); // 1 for the sku

        $value = $product->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'sugar', 'quantity' => '12', 'allergen' => false, 'additional_info' => 'text2', 'nutrition_score' => 'C'],
            $this->getLine($normalizedTable, 'sugar')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->tableNormalizer = $this->get(TableNormalizer::class);

        $this->createChannel([
            'code' => 'mobile',
            'category_tree' => 'master',
            'currencies' => ['USD'],
            'locales' => ['en_US', 'fr_FR'],
            'labels' => [],
        ]);

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_IMPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_IMPORT_JOB_CODE,
                'status' => 0,
                'type' => 'import',
                'raw_parameters' => 'a:5:{s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_IMPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_IMPORT_JOB_CODE,
                'status' => 0,
                'type' => 'import',
                'raw_parameters' => 'a:2:{s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";}',
            ]
        );

        $this->createTableAttribute(
            'nutrition',
            [
                [
                    'data_type' => SelectColumn::DATATYPE,
                    'code' => 'ingredient',
                    'options' => [
                        ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                        ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                        ['code' => 'egg', 'labels' => ['fr_FR' => 'Oeuf']],
                        ['code' => 'pepper'],
                    ],
                ],
                [
                    'data_type' => 'boolean',
                    'code' => 'allergen',
                ],
                [
                    'data_type' => 'number',
                    'code' => 'quantity',
                ],
                [
                    'data_type' => 'text',
                    'code' => 'additional_info',
                ],
                [
                    'data_type' => 'select',
                    'code' => 'nutrition_score',
                    'options' => [
                        ['code' => 'A'],
                        ['code' => 'B'],
                        ['code' => 'C'],
                        ['code' => 'D'],
                        ['code' => 'E'],
                    ]
                ],
                [
                    'data_type' => 'measurement',
                    'code' => 'weight',
                    'measurement_family_code' => 'Weight',
                    'measurement_default_unit_code' => 'KILOGRAM'
                ],
            ]
        );

        $this->createProduct('111111', [
            'nutrition' => [
                [
                    'locale' => 'fr_FR',
                    'scope' => 'mobile',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'quantity' => 50,
                            'allergen' => false,
                            'additional_info' => 'this is a text',
                            'nutrition_score' => 'B',
                        ],
                        [
                            'ingredient' => 'egg',
                            'quantity' => 23,
                            'allergen' => true,
                            'nutrition_score' => 'C',
                        ],
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'quantity' => 66,
                            'allergen' => true,
                            'additional_info' => 'this is a second text',
                            'nutrition_score' => 'B',
                        ],
                        [
                            'ingredient' => 'egg',
                            'quantity' => 20,
                            'allergen' => true,
                            'nutrition_score' => 'C',
                        ],
                    ],
                ],
            ],
        ]);
        $this->createProduct('111112', []);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function createChannel(array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors, $errors->__toString());

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    private function createTableAttribute(string $attributeCode, array $tableConfig): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => true,
                'scopable' => true,
                'table_configuration' => $tableConfig,
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createProduct(string $identifier, array $productValues): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'categories' => ['master'],
                'values' => $productValues,
            ]
        );

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getLine(array $normalizedTable, string $lineCode): ?array
    {
        foreach ($normalizedTable as $row) {
            if ($lineCode === ($row['ingredient'] ?? null)) {
                return $row;
            }
        }

        return null;
    }

    private function assertThereShouldBeAWarning(string $message): void
    {
        $reasons = $this->get('database_connection')->executeQuery(
            'SELECT reason FROM akeneo_batch_warning'
        )->fetchFirstColumn();

        self::assertContains($message, $reasons);
    }
}
