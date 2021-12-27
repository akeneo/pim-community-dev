<?php

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Export;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use PHPUnit\Framework\Assert;

final class ExportProductTableValuesWithLabelIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_product_table_values_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_product_table_values_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function itExportsTableValuesInCsv(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'en_US'];
        $csv = $this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, $config);

        $expected = <<<CSV
Product;Attribute;Ingredient;[quantity];"Is allergenic";[additional_info];"Nutrition score"
111111;"Nutrition (French France, [mobile])";Sugar;50;No;"this is a text";B
111111;"Nutrition (French France, [mobile])";[egg];23;Yes;;[C]
111111;"Nutrition (English United States, Ecommerce)";Sugar;66;Yes;"this is a second text";B
111111;"Nutrition (English United States, Ecommerce)";[egg];20;Yes;;[C]

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

        $reader = ReaderFactory::createFromType(Type::XLSX);
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
            ['Product', 'Attribute', 'Ingredient', '[quantity]', 'Is allergenic', '[additional_info]', 'Nutrition score'],
            ['111111', 'Nutrition (French France, [mobile])', 'Sugar', '50', 'No', 'this is a text', 'B'],
            ['111111', 'Nutrition (French France, [mobile])', '[egg]', '23', 'Yes', '', '[C]'],
            ['111111', 'Nutrition (English United States, Ecommerce)', 'Sugar', '66', 'Yes', 'this is a second text', 'B'],
            ['111111', 'Nutrition (English United States, Ecommerce)', '[egg]', '20', 'Yes', '', '[C]'],
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
                'raw_parameters' => 'a:7:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:7:"filters";a:1:{s:20:"table_attribute_code";s:9:"nutrition";}}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => static::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => static::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:6:{s:8:"filePath";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:7:"filters";a:1:{s:20:"table_attribute_code";s:9:"nutrition";}}',
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
                    'labels' => ['en_US' => 'Ingredient'],
                ],
                [
                    'data_type' => 'boolean',
                    'code' => 'allergen',
                    'labels' => ['fr_FR' => 'Est allergène', 'en_US' => 'Is allergenic']
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
                        ['code' => 'B', 'labels' => ['en_US' => 'B']],
                        ['code' => 'C'],
                        ['code' => 'D'],
                        ['code' => 'E'],
                    ],
                    'labels' => ['en_US' => 'Nutrition score']
                ],
            ],
        ]);

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

    private function createTableAttribute(string $attributeCode, array $data): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            \array_merge(
                [
                    'code' => $attributeCode,
                    'type' => AttributeTypes::TABLE,
                    'group' => 'other',
                    'localizable' => true,
                    'scopable' => true,
                ],
                $data
            )
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
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
