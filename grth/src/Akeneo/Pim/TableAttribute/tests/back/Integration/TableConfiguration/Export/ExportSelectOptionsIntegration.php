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

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Export;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ExportSelectOptionsIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_table_attribute_options_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_table_attribute_options_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function itExportsTableSelectOptionsInCsv(): void
    {
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);

        $expected = <<<CSV
attribute;column;code;label-en_US;label-fr_FR
nutrition;ingredient;egg;;Oeuf
nutrition;ingredient;salt;Salt;
nutrition;ingredient;sugar;Sugar;
packaging;dimension;depth;;
packaging;dimension;height;Height;
packaging;dimension;width;Width;Largeur

CSV;
        Assert::assertSame($expected, $csv);
    }

    /** @test */
    public function itExportsTableSelectOptionsInXlsx(): void
    {
        $bin = $this->jobLauncher->launchExport(self::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');

        $tmpfile = \tempnam(\sys_get_temp_dir(), 'test_table');
        \file_put_contents($tmpfile, $bin);

        $reader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX);
        $reader->open($tmpfile);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $rows = [];
        /** @var Row $row */
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        $reader->close();
        if (\is_file($tmpfile)) {
            \unlink($tmpfile);
        }

        $expected = [
            ['attribute', 'column', 'code', 'label-en_US', 'label-fr_FR'],
            ['nutrition', 'ingredient', 'egg', '', 'Oeuf'],
            ['nutrition', 'ingredient', 'salt', 'Salt', ''],
            ['nutrition', 'ingredient', 'sugar', 'Sugar', ''],
            ['packaging', 'dimension', 'depth', '', ''],
            ['packaging', 'dimension', 'height', 'Height', ''],
            ['packaging', 'dimension', 'width', 'Width', 'Largeur'],
        ];

        Assert::assertSame($expected, $rows);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

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
                'raw_parameters' => 'a:6:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:38:"/tmp/export_%job_label%_%datetime%.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:5:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";}s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;}',
            ]
        );

        $this->createTableAttribute(
            'packaging',
            [
                [
                    'data_type' => SelectColumn::DATATYPE,
                    'code' => 'dimension',
                    'options' => [
                        ['code' => 'width', 'labels' => ['en_US' => 'Width', 'fr_FR' => 'Largeur']],
                        ['code' => 'height', 'labels' => ['en_US' => 'Height']],
                        ['code' => 'depth'],
                    ],
                ],
                ['data_type' => NumberColumn::DATATYPE, 'code' => 'measure'],
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
                    'code' => 'is_allergenic',
                ],
            ]
        );
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
                'localizable' => false,
                'scopable' => false,
                'table_configuration' => $tableConfig,
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
