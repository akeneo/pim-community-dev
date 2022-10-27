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

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Import;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use Doctrine\DBAL\Connection;
use OpenSpout\Common\Entity\Row;
use PHPUnit\Framework\Assert;

class ImportSelectOptionsIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_table_attribute_options_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_table_attribute_options_import';

    private JobLauncher $jobLauncher;
    private SelectOptionCollectionRepository $optionCollectionRepository;
    private Connection $connection;

    /** @test */
    public function it_imports_table_attribute_options_from_a_csv_file(): void
    {
        $csv = <<<CSV
attribute;column;code;label-en_US
unknown;ingredient;test;
;ingredient;toto;
nutrition;unknown;titi;
nutrition;;foo;
nutrition;ingredient;;
nutrition;ingredient;invalid code;
nutrition;ingredient;foobar;abcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghio
nutrition;ingredient;tutu;label TUTU
nutrition;ingredient;new2;test
nutrition;ingredient;new2;toto
CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csv);

        $options = $this->optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredient'));
        Assert::assertEqualsCanonicalizing([
            ['code' => 'new2', 'labels' => ['en_US' => 'toto']],
            ['code' => 'tutu', 'labels' => ['en_US' => 'label TUTU']],
        ], $options->normalize());

        $warnings = $this->connection->executeQuery('SELECT reason FROM akeneo_batch_warning')->fetchAllAssociative();

        Assert::assertEqualsCanonicalizing([
            'attribute: The "unknown" attribute does not exist' . PHP_EOL,
            'Field "attribute" must be filled',
            'column: The "unknown" column does not exist for the "nutrition" attribute' . PHP_EOL,
            'Field "column" must be filled',
            'Field "code" must be filled',
            'optionCode: The option code can only contain letters, numbers and underscores: invalid code' . PHP_EOL,
            'labels[en_US]: The option label is too long: it must be 255 characters or less: abcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghioabcdefghio' . PHP_EOL,
        ], array_map(fn (array $warningRow): string => $warningRow['reason'], $warnings));
    }

    /** @test */
    public function it_imports_table_attributes_from_an_xlsx_file(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_import');
        $writer = SpoutWriterFactory::create(SpoutWriterFactory::XLSX);
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            [
                Row::fromValues(['attribute', 'column', 'code', 'label-en_US']),
                Row::fromValues([
                    'nutrition',
                    'ingredient',
                    'salt',
                    'Salt',
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

        $options = $this->optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredient'));
        Assert::assertEqualsCanonicalizing([
            ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
        ], $options->normalize());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->optionCollectionRepository = $this->get(SelectOptionCollectionRepository::class);
        $this->connection = $this->get('database_connection');

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
