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

namespace Akeneo\Pim\TableAttribute\tests\back\Integration\TableConfiguration\Import;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Box\Spout\Writer\WriterFactory;
use PHPUnit\Framework\Assert;

class ImportTableAttributeIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_attribute_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_attribute_import';

    private JobLauncher $jobLauncher;
    private AttributeRepositoryInterface $attributeRepository;

    /** @test */
    public function it_imports_table_attributes_from_a_csv_file(): void
    {
        $csv = <<<CSV
code;type;localizable;scopable;group;unique;sort_order;table_configuration
nutrition;pim_catalog_table;0;0;other;0;2;[{"code":"ingredients","data_type":"select","labels":{"en_US":"Ingredients"}},{"code":"quantity","data_type":"text","labels":{"en_US":"Quantity"}}]
storage;pim_catalog_table;0;0;other;0;3;[{"code":"dimension","data_type":"select","labels":{"en_US":"Dimension"}},{"code":"value","data_type":"text","labels":{"en_US":"Value"}}]
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $nutritionAttribute = $this->attributeRepository->findOneByIdentifier('nutrition');
        Assert::assertNotNull($nutritionAttribute);
        // TODO Import validations
        Assert::assertEqualsCanonicalizing(
            [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'], 'validations' => (object) []],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => ['en_US' => 'Quantity'], 'validations' => (object) []],
            ],
            $nutritionAttribute->getRawTableConfiguration()
        );

        $storageAttribute = $this->attributeRepository->findOneByIdentifier('storage');
        Assert::assertNotNull($storageAttribute);
        Assert::assertSame(AttributeTypes::TABLE, $storageAttribute->getType());
        Assert::assertEqualsCanonicalizing(
            [
                ['code' => 'dimension', 'data_type' => 'select', 'labels' => ['en_US' => 'Dimension'], 'validations' => (object) []],
                ['code' => 'value', 'data_type' => 'text', 'labels' => ['en_US' => 'Value'], 'validations' => (object) []],
            ],
            $storageAttribute->getRawTableConfiguration()
        );
    }

    /** @test */
    public function it_imports_table_attributes_from_an_xlsx_file(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_import');
        $writer = WriterFactory::create('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            [
                ['code', 'type', 'localizable', 'scopable', 'group', 'unique', 'sort_order', 'table_configuration'],
                [
                    'nutrition',
                    'pim_catalog_table',
                    '0',
                    '0',
                    'other',
                    '0',
                    '5',
                    '[{"code":"ingredients","data_type":"select","labels":{"en_US":"Ingredients"}},{"code":"quantity","data_type":"text","labels":{"en_US":"Quantity"}}]',
                ],
                [
                    'storage',
                    'pim_catalog_table',
                    '0',
                    '0',
                    'other',
                    '0',
                    '6',
                    '[{"code":"dimension","data_type":"select","labels":{"en_US":"Dimension"}},{"code":"value","data_type":"text","labels":{"en_US":"Value"}}]',
                ],
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

        $nutritionAttribute = $this->attributeRepository->findOneByIdentifier('nutrition');
        Assert::assertNotNull($nutritionAttribute);
        // TODO Import validations
        Assert::assertEqualsCanonicalizing(
            [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'], 'validations' => (object) []],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => ['en_US' => 'Quantity'], 'validations' => (object) []],
            ],
            $nutritionAttribute->getRawTableConfiguration()
        );

        $storageAttribute = $this->attributeRepository->findOneByIdentifier('storage');
        Assert::assertNotNull($storageAttribute);
        Assert::assertSame(AttributeTypes::TABLE, $storageAttribute->getType());
        Assert::assertEqualsCanonicalizing(
            [
                ['code' => 'dimension', 'data_type' => 'select', 'labels' => ['en_US' => 'Dimension'], 'validations' => (object) []],
                ['code' => 'value', 'data_type' => 'text', 'labels' => ['en_US' => 'Value'], 'validations' => (object) []],
            ],
            $storageAttribute->getRawTableConfiguration()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->attributeRepository = $this->get('pim_catalog.repository.attribute');

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
                        'code' => 'ingredients',
                        'data_type' => 'select',
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'text',
                    ],
                ]
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
