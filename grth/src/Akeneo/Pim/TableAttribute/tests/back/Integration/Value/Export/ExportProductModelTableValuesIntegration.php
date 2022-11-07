<?php

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Export;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ExportProductModelTableValuesIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_product_model_table_values_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_product_model_table_values_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function itExportsProductModelTableValuesInCsv(): void
    {
        $csv = $this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, []);

        $expected = <<<CSV
product_model;attribute;ingredient;allergen;quantity;additional_info;nutrition_score
111111;nutrition-fr_FR-mobile;sugar;0;50;"this is a text";B
111111;nutrition-fr_FR-mobile;egg;1;23;;C
111111;nutrition-en_US-ecommerce;sugar;1;66;"this is a second text";B
111111;nutrition-en_US-ecommerce;egg;1;20;;C

CSV;
        Assert::assertSame($expected, $csv);
    }

    /** @test */
    public function itExportsProductModelTableValuesInXlsx(): void
    {
        $bin = $this->jobLauncher->launchExport(static::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');

        $tmpfile = \tempnam(\sys_get_temp_dir(), 'test_table_values');
        \file_put_contents($tmpfile, $bin);

        $reader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX);
        $reader->open($tmpfile);
        $sheet = \current(\iterator_to_array($reader->getSheetIterator()));
        $actualRows = \array_map(
            fn (Row $row): array => $row->toArray(),
            \iterator_to_array($sheet->getRowIterator())
        );
        $reader->close();
        if (\is_file($tmpfile)) {
            \unlink($tmpfile);
        }

        $expected = [
            ['product_model', 'attribute', 'ingredient', 'allergen', 'quantity', 'additional_info', 'nutrition_score'],
            ['111111', 'nutrition-fr_FR-mobile', 'sugar', '0', '50', 'this is a text', 'B'],
            ['111111', 'nutrition-fr_FR-mobile', 'egg', '1', '23', '', 'C'],
            ['111111', 'nutrition-en_US-ecommerce', 'sugar', '1', '66', 'this is a second text', 'B'],
            ['111111', 'nutrition-en_US-ecommerce', 'egg', '1', '20', '', 'C'],
        ];

        Assert::assertSame($expected, \array_values($actualRows));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->createChannel([
            'code' => 'mobile',
            'category_tree' => 'master',
            'currencies' => ['USD'],
            'locales' => ['en_US', 'fr_FR'],
            'labels' => [],
        ]);

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => static::CSV_EXPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => static::CSV_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:7:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:38:"/tmp/export_%job_label%_%datetime%.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:7:"filters";a:1:{s:20:"table_attribute_code";s:9:"nutrition";}}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => static::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => static::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:6:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";}s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:7:"filters";a:1:{s:20:"table_attribute_code";s:9:"nutrition";}}',
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

    private function createProductModel(string $code, array $productModelValues): void
    {
        $productModel = new ProductModel();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'parent' => null,
                'family_variant' => 'shoe_size',
                'categories' => ['master'],
                'values' => $productModelValues,
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

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }
}
