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

namespace Akeneo\Test\Pim\TableAttribute\Enterprise\Integration\TableConfiguration\Export;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

final class ExportTableAttributeIntegration extends TestCase
{
    use EntityBuilderTrait;

    private const CSV_EXPORT_JOB_CODE = 'csv_attribute_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_attribute_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function it_exports_a_table_attribute_in_csv(): void
    {
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);
        $formatted = \array_map(
            fn (string $row): array => \str_getcsv($row, ';'),
            \array_filter(\explode(PHP_EOL, $csv))
        );
        $header = \array_shift($formatted);

        $expectedConfig = \json_encode(
            [
                [
                    'code' => 'ingredients',
                    'data_type' => 'select',
                    'labels' => [
                        'en_US' => 'Ingredients',
                    ],
                    'validations' => [],
                    'is_required_for_completeness' => true,
                    'options' => [],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [
                        'en_US' => 'Quantity',
                    ],
                    'validations' => [
                        'min' => 10,
                        'max' => 200,
                        'decimals_allowed' => true,
                    ],
                    'is_required_for_completeness' => true,
                ],
                [
                    'code' => 'brand',
                    'data_type' => 'reference_entity',
                    'labels' => [
                        'en_US' => 'Brand',
                    ],
                    'validations' => [],
                    'is_required_for_completeness' => false,
                    'reference_entity_identifier' => 'brands',
                ],
            ]
        );

        Assert::assertCount(2, $formatted);
        foreach ($formatted as $row) {
            $row = \array_combine($header, $row);
            if ('nutrition' !== $row['code']) {
                Assert::assertSame('', $row['table_configuration']);
            } else {
                Assert::assertEquals(new \stdClass(), \json_decode($row['table_configuration'], false)[0]->validations);
                Assert::assertJsonStringEqualsJsonString($expectedConfig, $this->removeIds($row['table_configuration']));
            }
        }
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

        $expectedConfig = \json_encode(
            [
                [
                    'code' => 'ingredients',
                    'data_type' => 'select',
                    'labels' => [
                        'en_US' => 'Ingredients',
                    ],
                    'validations' => [],
                    'is_required_for_completeness' => true,
                    'options' => [],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [
                        'en_US' => 'Quantity',
                    ],
                    'validations' => [
                        'min' => 10,
                        'max' => 200,
                        'decimals_allowed' => true,
                    ],
                    'is_required_for_completeness' => true,
                ],
                [
                    'code' => 'brand',
                    'data_type' => 'reference_entity',
                    'labels' => [
                        'en_US' => 'Brand',
                    ],
                    'validations' => [],
                    'is_required_for_completeness' => false,
                    'reference_entity_identifier' => 'brands',
                ],
            ]
        );

        Assert::assertCount(2, $lines);
        foreach ($lines as $row) {
            $row = \array_combine($header->toArray(), $row->toArray());
            if ('nutrition' !== $row['code']) {
                Assert::assertSame('', $row['table_configuration']);
            } else {
                Assert::assertEquals(new \stdClass(), \json_decode($row['table_configuration'], false)[0]->validations);
                Assert::assertJsonStringEqualsJsonString($expectedConfig, $this->removeIds($row['table_configuration']));
            }
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

        $this->createReferenceEntity('brands');
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredients',
                    'data_type' => 'select',
                    'labels' => [
                        'en_US' => 'Ingredients',
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [
                        'en_US' => 'Quantity',
                    ],
                    'validations' => [
                        'min' => 10,
                        'max' => 200,
                        'decimals_allowed' => true,
                    ],
                    'is_required_for_completeness' => true,
                ],
                [
                    'code' => 'brand',
                    'data_type' => 'reference_entity',
                    'labels' => [
                        'en_US' => 'Brand',
                    ],
                    'validations' => [],
                    'is_required_for_completeness' => false,
                    'reference_entity_identifier' => 'brands',
                ],
            ],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));

        $this->get('pim_catalog.saver.attribute')->save($attribute);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function removeIds(string $rawTableConfiguration): string
    {
        $decoded = \json_decode($rawTableConfiguration, true);
        if (null === $decoded) {
            return $rawTableConfiguration;
        }

        foreach (array_keys($decoded) as $index) {
            unset($decoded[$index]['id']);
        }

        return \json_encode($decoded);
    }
}
