<?php

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Export;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ExportProductTableValuesWithLabelIntegration extends TestCase
{
    use EntityBuilderTrait;

    private const CSV_EXPORT_JOB_CODE = 'csv_product_table_values_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_product_table_values_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function itExportsTableValuesInCsv(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'en_US'];
        $csv = $this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, $config);

        $expected = <<<CSV
Product;Attribute;Ingredient;[quantity];"Is allergenic";[additional_info];"Nutrition score";Weight
111111;"Nutrition (French France, [mobile])";Sugar;50;No;"this is a text";B;"42 Kilogram"
111111;"Nutrition (French France, [mobile])";[egg];23;Yes;;[C];"69 Milligram"
111111;"Nutrition (English United States, Ecommerce)";Sugar;66;Yes;"this is a second text";B;"55 Kilogram"
111111;"Nutrition (English United States, Ecommerce)";[egg];20;Yes;;[C];

CSV;
        Assert::assertSame($expected, $csv);
    }

    /** @test */
    public function itExportsTableValuesInCsvInFr(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'fr_FR'];
        $csv = $this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, $config);

        $expected = <<<CSV
Produit;Attribut;ingredient;[quantity];"Est allergène";[additional_info];[nutrition_score];Poids
111111;"foo (français France, [mobile])";[sugar];50;Non;"this is a text";[B];"42 Kilogramme"
111111;"foo (français France, [mobile])";Oeuf;23;Oui;;[C];"69 Milligramme"
111111;"foo (anglais États-Unis, Ecommerce)";[sugar];66;Oui;"this is a second text";[B];"55 Kilogramme"
111111;"foo (anglais États-Unis, Ecommerce)";Oeuf;20;Oui;;[C];

CSV;
        Assert::assertSame($expected, $csv);
    }

    /** @test */
    public function itExportsTableValuesInXlsx(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'en_US'];
        $bin = $this->jobLauncher->launchExport(static::XLSX_EXPORT_JOB_CODE, null, $config, 'xlsx');

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
            ['Product', 'Attribute', 'Ingredient', '[quantity]', 'Is allergenic', '[additional_info]', 'Nutrition score', 'Weight'],
            ['111111', 'Nutrition (French France, [mobile])', 'Sugar', '50', 'No', 'this is a text', 'B', '42 Kilogram'],
            ['111111', 'Nutrition (French France, [mobile])', '[egg]', '23', 'Yes', '', '[C]', '69 Milligram'],
            ['111111', 'Nutrition (English United States, Ecommerce)', 'Sugar', '66', 'Yes', 'this is a second text', 'B', '55 Kilogram'],
            ['111111', 'Nutrition (English United States, Ecommerce)', '[egg]', '20', 'Yes', '', '[C]', ''],
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
            'currencies' => ['USD'],
            'locales' => ['en_US', 'fr_FR'],
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

        $this->createTableAttribute('nutrition', [
            'labels' => ['en_US' => 'Nutrition', 'fr_FR' => 'foo'],
            'table_configuration' => [
                [
                    'data_type' => SelectColumn::DATATYPE,
                    'code' => 'ingredient',
                    'options' => [
                        ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                        ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                        ['code' => 'egg', 'labels' => ['fr_FR' => 'Oeuf']],
                    ],
                    'labels' => ['en_US' => 'Ingredient', 'fr_FR' => 'ingredient'],
                ],
                [
                    'data_type' => 'number',
                    'code' => 'quantity',
                ],
                [
                    'data_type' => 'boolean',
                    'code' => 'allergen',
                    'labels' => ['fr_FR' => 'Est allergène', 'en_US' => 'Is allergenic']
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
                        ['code' => 'B', 'labels' => ['en_US' => 'B']],
                        ['code' => 'C'],
                        ['code' => 'D'],
                        ['code' => 'E'],
                    ],
                    'labels' => ['en_US' => 'Nutrition score']
                ],
                [
                    'data_type' => 'measurement',
                    'code' => 'weight',
                    'measurement_family_code' => 'Weight',
                    'measurement_default_unit_code' => 'KILOGRAM',
                    'labels' => ['en_US' => 'Weight', 'fr_FR' => 'Poids'],
                ],
            ],
        ]);

        $this->createProduct('111111', [
            'categories' => ['master'],
            'values' => [
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
                                'weight' => [
                                    'amount' => '42',
                                    'unit' => 'KILOGRAM'
                                ],
                            ],
                            [
                                'ingredient' => 'egg',
                                'quantity' => 23,
                                'allergen' => true,
                                'nutrition_score' => 'C',
                                'weight' => [
                                    'amount' => '69',
                                    'unit' => 'MILLIGRAM'
                                ],
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
                                'weight' => [
                                    'amount' => '55',
                                    'unit' => 'KILOGRAM'
                                ],
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
            ],
        ]);
    }

    private function createTableAttribute(string $attributeCode, array $data): void
    {
        $this->createAttribute(\array_merge(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => true,
                'scopable' => true,
            ],
            $data
        ));
    }
}
