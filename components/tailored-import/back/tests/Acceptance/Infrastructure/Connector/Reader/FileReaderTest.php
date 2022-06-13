<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\Infrastructure\Connector\Reader;

use Akeneo\Platform\TailoredImport\Domain\Exception\MismatchedFileHeadersException;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\FileReader;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;

class FileReaderTest extends AcceptanceTestCase
{
    private const DEFAULT_COLUMN_CONFIGURATION = [
        ['label' => 'Sku', 'uuid' => '25621f5a-504f-4893-8f0c-9f1b0076e53e', 'index' => 0],
        ['label' => 'Name', 'uuid' => '2d9e967a-5efa-4a31-a254-99f7c50a145c', 'index' => 1],
        ['label' => 'Price', 'uuid' => '2f51f41a-780c-4794-a86d-ad2759ae57c4', 'index' => 2],
        ['label' => 'Enabled', 'uuid' => 'c35ad6b1-c8c2-45e8-a3fd-374a51dee12f', 'index' => 3],
        ['label' => 'Release date', 'uuid' => 'bbbbead8-4f74-4bf1-8760-2bf67b7b2317', 'index' => 4],
        ['label' => 'Price with tax', 'uuid' => 'f3a1e9f4-e17d-4345-af4d-a9f1be778180', 'index' => 5],
    ];

    /**
     * @test
     */
    public function it_returns_a_list_of_cell_from_a_file(): void
    {
        $expectedData = [
            new RowPayload(
                new Row([
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref1',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 1',
                    '2f51f41a-780c-4794-a86d-ad2759ae57c4' => '12',
                    'c35ad6b1-c8c2-45e8-a3fd-374a51dee12f' => 'TRUE',
                    'bbbbead8-4f74-4bf1-8760-2bf67b7b2317' => '3/22/2022',
                    'f3a1e9f4-e17d-4345-af4d-a9f1be778180' => '14.4'
                ]),
                ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
                1
            ),
            new RowPayload(
                new Row([
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref2',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 2',
                    '2f51f41a-780c-4794-a86d-ad2759ae57c4' => '13.87',
                    'c35ad6b1-c8c2-45e8-a3fd-374a51dee12f' => 'FALSE',
                    'bbbbead8-4f74-4bf1-8760-2bf67b7b2317' => '5/23/2022',
                    'f3a1e9f4-e17d-4345-af4d-a9f1be778180' => ''
                ]),
                ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
                2
            ),
            new RowPayload(
                new Row([
                    '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref3',
                    '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 3',
                    '2f51f41a-780c-4794-a86d-ad2759ae57c4' => '16',
                    'c35ad6b1-c8c2-45e8-a3fd-374a51dee12f' => 'TRUE',
                    'bbbbead8-4f74-4bf1-8760-2bf67b7b2317' => '10/5/2015',
                    'f3a1e9f4-e17d-4345-af4d-a9f1be778180' => '19.2'
                ]),
                ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
                3
            )
        ];

        $actualData = $this->launchRead();
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @test
     */
    public function it_skip_row_that_contain_out_of_bound_data(): void
    {
        $this->expectException(InvalidItemException::class);
        $this->launchRead(sheetName: 'Out of bound value');
    }

    /**
     * @test
     */
    public function it_check_that_file_column_match_with_column_configuration(): void
    {
        $this->expectExceptionObject(new MismatchedFileHeadersException(
            ['Sku','Price','Name','Enabled','Release date','Price with tax'],
            ['Sku','Name','Price','Enabled','Release date','Price with tax'],
        ));
        $columnConfiguration = [
            ['label' => 'Sku', 'uuid' => '25621f5a-504f-4893-8f0c-9f1b0076e53e', 'index' => 0],
            ['label' => 'Price', 'uuid' => '2f51f41a-780c-4794-a86d-ad2759ae57c4', 'index' => 1],
            ['label' => 'Name', 'uuid' => '2d9e967a-5efa-4a31-a254-99f7c50a145c', 'index' => 2],
            ['label' => 'Enabled', 'uuid' => 'c35ad6b1-c8c2-45e8-a3fd-374a51dee12f', 'index' => 3],
            ['label' => 'Release date', 'uuid' => 'bbbbead8-4f74-4bf1-8760-2bf67b7b2317', 'index' => 4],
            ['label' => 'Price with tax', 'uuid' => 'f3a1e9f4-e17d-4345-af4d-a9f1be778180', 'index' => 5],
        ];

        $this->launchRead(columnConfiguration: $columnConfiguration);
    }

    private function launchRead(
        string $sheetName = 'Products',
        array $columnConfiguration = self::DEFAULT_COLUMN_CONFIGURATION
    ): array {
        $stepExecution = $this->getStepExecution($sheetName, $columnConfiguration);
        $reader = $this->getReader($stepExecution);

        $readData = [];
        while ($data = $reader->read()) {
            $readData[] = $data;
        }

        $reader->flush();

        return $readData;
    }

    private function getStepExecution(
        string $sheetName = 'Products',
        array $columnConfiguration = self::DEFAULT_COLUMN_CONFIGURATION
    ): StepExecution {
        $jobParameters = new JobParameters([
            'file_structure' => [
                'header_row' => 1,
                'first_column' => 0,
                'first_product_row' => 2,
                'sheet_name' => $sheetName,
                'unique_identifier_column' => 0,
            ],
            'filePath' => 'components/tailored-import/back/tests/Common/simple_import.xlsx',
            'import_structure' => [
                'columns' => $columnConfiguration
            ]
        ]);

        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);

        return new StepExecution('step_name', $jobExecution);
    }

    private function getReader(StepExecution $stepExecution): FileReader
    {
        $reader = $this->get('akeneo.tailored_import.reader.file.xlsx');
        $reader->setStepExecution($stepExecution);
        $reader->initialize();

        return $reader;
    }
}
