<?php

namespace Akeneo\Pim\TableAttribute\tests\back\Enterprise\Integration\Value\Export;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
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
Product;Attribute;Brand;Color
111111;"Nutrition (French France, [mobile])";Akeneo;Red
111111;"Nutrition (French France, [mobile])";[apple];[blue]
111111;"Nutrition (English United States, Ecommerce)";Akeneo;

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
            ['Product', 'Attribute', 'Brand', 'Color'],
            ['111111', 'Nutrition (French France, [mobile])', 'Akeneo', 'Red'],
            ['111111', 'Nutrition (French France, [mobile])', '[apple]', '[blue]'],
            ['111111', 'Nutrition (English United States, Ecommerce)', 'Akeneo', ''],
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

        $this->createReferenceEntity('brand');
        $this->createReferenceEntity('color');

        $this->createRecord('brand', 'akeneo', ['fr_FR' => 'Akeneo', 'en_US' => 'Akeneo']);
        $this->createRecord('brand', 'apple', []);
        $this->createRecord('color', 'red', ['en_US' => 'Red']);
        $this->createRecord('color', 'blue', []);

        $this->createTableAttribute('nutrition', [
            'labels' => ['en_US' => 'Nutrition', 'fr_FR' => 'foo'],
            'table_configuration' => [
                [
                    'data_type' => ReferenceEntityColumn::DATATYPE,
                    'code' => 'brand',
                    'labels' => ['en_US' => 'Brand'],
                    'reference_entity_identifier' => 'brand'
                ],
                [
                    'data_type' => ReferenceEntityColumn::DATATYPE,
                    'code' => 'color',
                    'labels' => ['en_US' => 'Color'],
                    'reference_entity_identifier' => 'color'
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
                                'brand' => 'akeneo',
                                'color' => 'red',
                            ],
                            [
                                'brand' => 'apple',
                                'color' => 'blue'
                            ],
                        ],
                    ],
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => [
                            [
                                'brand' => 'akeneo',
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
