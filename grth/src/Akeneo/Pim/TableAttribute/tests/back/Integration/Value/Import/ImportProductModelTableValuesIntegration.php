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
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ImportProductModelTableValuesIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_product_model_table_values_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_product_model_table_values_import';

    private JobLauncher $jobLauncher;
    private ProductModelRepositoryInterface $productModelRepository;
    private TableNormalizer $tableNormalizer;

    /** @test */
    public function it_imports_product_model_table_values_in_csv(): void
    {
        $csv = <<<CSV
product_model;attribute;ingredient;quantity;allergen;additional_info;nutrition_score
111111;nutrition-en_US-ecommerce;salt;20;1;text;A
111111;nutrition-en_US-ecommerce;sugar;24;0;text2;B
111111;nutrition-fr_FR-mobile;egg;12;;text3;C
111111;nutrition-fr_FR-mobile;pepper;;1;text4;D
111111;nutrition-en_US-mobile;pepper;;1;text5;D
111112;nutrition-en_US-mobile;pepper;;;;
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $productModel = $this->productModelRepository->findOneByIdentifier('111111');
        Assert::assertNotNull($productModel);
        Assert::assertCount(3, $productModel->getValues());

        $value = $productModel->getValue('nutrition', 'en_US', 'ecommerce');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'salt', 'quantity' => '20', 'allergen' => true, 'additional_info' => 'text', 'nutrition_score' => 'A'],
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

        $value = $productModel->getValue('nutrition', 'fr_FR', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'egg', 'quantity' => '12', 'additional_info' => 'text3', 'nutrition_score' => 'C'],
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

        $value = $productModel->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper', 'allergen' => true, 'additional_info' => 'text5', 'nutrition_score' => 'D'],
            $this->getLine($normalizedTable, 'pepper')
        );

        $productModel = $this->productModelRepository->findOneByIdentifier('111112');
        Assert::assertNotNull($productModel);
        Assert::assertCount(1, $productModel->getValues());

        $value = $productModel->getValue('nutrition', 'en_US', 'mobile');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(1, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'pepper'],
            $this->getLine($normalizedTable, 'pepper')
        );
    }

    /** @test */
    public function it_imports_table_attributes_from_an_xlsx_file(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_import');
        $writer = SpoutWriterFactory::create(SpoutWriterFactory::XLSX);
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            \array_map(
                static fn (array $data): Row => Row::fromValues($data),
                [
                    ['product_model', 'attribute', 'ingredient', 'quantity', 'allergen', 'additional_info', 'nutrition_score'],
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

        $product = $this->productModelRepository->findOneByIdentifier('111111');
        Assert::assertNotNull($product);
        Assert::assertCount(2, $product->getValues());

        $value = $product->getValue('nutrition', 'en_US', 'ecommerce');
        Assert::assertNotNull($value);
        $normalizedTable = $this->tableNormalizer->normalize($value->getData());
        Assert::assertCount(3, $normalizedTable);
        Assert::assertEquals(
            ['ingredient' => 'salt', 'quantity' => '20', 'allergen' => true, 'additional_info' => 'text', 'nutrition_score' => 'A'],
            $this->getLine($normalizedTable, 'salt')
        );

        $product = $this->productModelRepository->findOneByIdentifier('111112');
        Assert::assertNotNull($product);
        Assert::assertCount(1, $product->getValues());

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
        $this->productModelRepository = $this->get('pim_catalog.repository.product_model');
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
            ]
        );

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

        $this->createProductModel('111111', [
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
        $this->createProductModel('111112', []);
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

    private function createProductModel(string $identifier, array $productModelValues): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create($identifier);
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $identifier,
                'categories' => ['master'],
                'family_variant' => 'shoe_size',
                'values' => $productModelValues,
            ]
        );

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
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
}
