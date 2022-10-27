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

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ExportTableValueIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_product_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_product_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function it_exports_a_table_value_in_csv(): void
    {
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);
        $expectedContent = <<<CSV
sku;categories;enabled;family;groups;nutrition
toto;master;1;;;"[{""ingredient"":""salt"",""is_allergenic"":false},{""ingredient"":""egg"",""quantity"":2},{""ingredient"":""butter"",""quantity"":25,""is_allergenic"":true}]"

CSV;
        Assert::assertSame($expectedContent, $csv);
    }

    /** @test */
    public function it_exports_a_table_attribute_in_xlsx(): void
    {
        $bin = $this->jobLauncher->launchExport(self::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');
        $tmpfile = \tempnam(\sys_get_temp_dir(), 'test_table');
        \file_put_contents($tmpfile, $bin);

        $reader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX);
        $reader->open($tmpfile);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        /** @var Row[] $lines */
        $lines = iterator_to_array($sheet->getRowIterator());
        $reader->close();
        if (\is_file($tmpfile)) {
            \unlink($tmpfile);
        }
        $header = \array_shift($lines);

        $expectedNutritionValue = '[{"ingredient":"salt","is_allergenic":false},{"ingredient":"egg","quantity":2},{"ingredient":"butter","quantity":25,"is_allergenic":true}]';

        Assert::assertCount(1, $lines);
        foreach ($lines as $row) {
            $row = \array_combine($header->toArray(), $row->toArray());
            Assert::assertSame($expectedNutritionValue, $row['nutrition']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_EXPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:13:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:38:"/tmp/export_%job_label%_%datetime%.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:0;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:3:"ALL";s:5:"value";N;}i:1;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}i:2;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}s:9:"structure";a:2:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:12:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";}s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:3:"ALL";s:5:"value";N;}i:1;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}i:2;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}s:9:"structure";a:2:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}',
            ]
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

        $this->createProduct('toto', [
            ['ingredient' => 'salt', 'is_allergenic' => false],
            ['ingredient' => 'egg', 'quantity' => 2],
            ['ingredient' => 'butter', 'quantity' => 25, 'is_allergenic' => true],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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
}
