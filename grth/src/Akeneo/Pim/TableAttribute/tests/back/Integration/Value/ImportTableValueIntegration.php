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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\Common\Creator\WriterFactory;
use PHPUnit\Framework\Assert;

final class ImportTableValueIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_product_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_product_import';

    private array $columnIdsIndexedByCode = [];
    private JobLauncher $jobLauncher;

    /** @test */
    public function itImportsTableAttributeValuesFromACsvFile(): void
    {
        $csv = <<<CSV
sku;nutrition
test1;[{"ingredient":"salt","quantity":10}]
test2;
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $test1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test1');
        Assert::assertInstanceOf(ProductInterface::class, $test1);
        $nutritionValue = $test1->getValue('nutrition');
        Assert::assertNotNull($nutritionValue);
        Assert::assertEquals(
            Table::fromNormalized([
                [
                    $this->columnIdsIndexedByCode['ingredient'] => 'salt',
                    $this->columnIdsIndexedByCode['quantity'] => 10,
                ],
            ]),
            $nutritionValue->getData()
        );

        $test2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test2');
        Assert::assertInstanceOf(ProductInterface::class, $test2);
        Assert::assertNull($test2->getValue('nutrition'));
    }

    /** @test */
    public function itImportsTableAttributeValuesFromAnXlsxFile(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_import');
        $writer = WriterFactory::createFromType('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            [
                WriterEntityFactory::createRowFromArray(['sku', 'nutrition']),
                WriterEntityFactory::createRowFromArray([
                    'test1',
                    '[{"ingredient":"salt","quantity":10}]',
                ]),
                WriterEntityFactory::createRowFromArray([
                    'test2',
                    '',
                ]),
            ]
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

        $test1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test1');
        Assert::assertInstanceOf(ProductInterface::class, $test1);
        $nutritionValue = $test1->getValue('nutrition');
        Assert::assertNotNull($nutritionValue);
        Assert::assertEquals(
            Table::fromNormalized([
                [
                    $this->columnIdsIndexedByCode['ingredient'] => 'salt',
                    $this->columnIdsIndexedByCode['quantity'] => 10,
                ],
            ]),
            $nutritionValue->getData()
        );

        $test2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('test2');
        Assert::assertInstanceOf(ProductInterface::class, $test2);
        Assert::assertNull($test2->getValue('nutrition'));
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
                        'options' => [
                            ['code' => 'salt'],
                        ],
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'number',
                    ],
                ],
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $res = $this->get('database_connection')->executeQuery(
            'SELECT id, code FROM pim_catalog_table_column'
        )->fetchAllAssociative();
        foreach ($res as $row) {
            $this->columnIdsIndexedByCode[$row['code']] = $row['id'];
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
