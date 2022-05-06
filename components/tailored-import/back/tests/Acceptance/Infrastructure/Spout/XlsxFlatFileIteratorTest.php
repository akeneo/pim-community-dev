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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\Infrastructure\Spout;

use Akeneo\Platform\TailoredImport\Domain\Exception\FileNotFoundException;
use Akeneo\Platform\TailoredImport\Domain\Exception\SheetNotFoundException;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileIterator;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;

class XlsxFlatFileIteratorTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_iterate_over_file_content(): void
    {
        $flatFileIterator = $this->getFlatFileIterator();
        $actualFileContent = iterator_to_array($flatFileIterator);
        $expectedFileContent = [
            1 => ['ref1', 'Produit 1', '12', 'TRUE', '3/22/2022', '14.4'],
            2 => ['ref2', 'Produit 2', '13.87', 'FALSE', '5/23/2022', ''],
            3 => ['ref3', 'Produit 3', '16', 'TRUE', '10/5/2015', '19.2'],
        ];

        $this->assertEquals($expectedFileContent, $actualFileContent);
    }

    /**
     * @test
     */
    public function it_returns_the_headers(): void
    {
        $actualFileHeaders = $this->getFlatFileIterator()->getHeaders();
        $expectedFileHeaders = FileHeaderCollection::createFromNormalized([
            ['index' => 0, 'label' => 'Sku'],
            ['index' => 1, 'label' => 'Name'],
            ['index' => 2, 'label' => 'Price'],
            ['index' => 3, 'label' => 'Enabled'],
            ['index' => 4, 'label' => 'Release date'],
            ['index' => 5, 'label' => 'Price with tax']
        ]);

        $this->assertEquals($expectedFileHeaders, $actualFileHeaders);
    }

    /**
     * @test
     */
    public function it_can_iterate_on_file_with_empty_columns_and_lines(): void
    {
        $flatFileIterator = $this->getFlatFileIterator(
            headerLine: 2,
            firstColumn: 1,
            productLine: 4,
            sheetName: 'Empty lines and columns',
        );

        $actualFileHeader = $flatFileIterator->getHeaders();
        $expectedFileHeader = FileHeaderCollection::createFromNormalized([
            ['index' => 1, 'label' => 'Sku'],
            ['index' => 2, 'label' => 'Name'],
            ['index' => 3, 'label' => 'Price'],
            ['index' => 4, 'label' => 'Enabled'],
            ['index' => 5, 'label' => 'Release date'],
            ['index' => 6, 'label' => 'Price with tax']
        ]);

        $this->assertEquals($expectedFileHeader, $actualFileHeader);

        $actualFileContent = iterator_to_array($flatFileIterator);
        $expectedFileContent = [
            1 => ['ref1', 'Produit 1', '12', 'TRUE', '3/22/2022', '14.4'],
            2 => ['ref2', 'Produit 2', '13.87', 'FALSE', '5/23/2022', ''],
            3 => ['ref3', 'Produit 3', '16', 'TRUE', '10/5/2015', '19.2'],
        ];

        $this->assertEquals($expectedFileContent, $actualFileContent);
    }

    /**
     * @test
     */
    public function it_returns_the_headers_even_with_empty_trailing_header(): void
    {
        $flatFileIterator = $this->getFlatFileIterator(
            headerLine: 1,
            firstColumn: 0,
            productLine: 2,
            sheetName: 'Trailing empty header',
        );

        $actualFileHeader = $flatFileIterator->getHeaders();
        $expectedFileHeader = FileHeaderCollection::createFromNormalized([
            ['index' => 0, 'label' => 'Sku'],
            ['index' => 1, 'label' => 'Name'],
            ['index' => 2, 'label' => 'Price'],
            ['index' => 3, 'label' => 'Enabled'],
            ['index' => 4, 'label' => 'Release date'],
            ['index' => 5, 'label' => 'Price with tax']
        ]);

        $this->assertEquals($expectedFileHeader, $actualFileHeader);
    }

    /**
     * @test
     */
    public function it_throw_an_exception_when_file_is_not_found(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->getFlatFileIterator(filePath: 'non_existent_file');
    }

    /**
     * @test
     */
    public function it_throw_an_exception_when_sheet_is_not_found(): void
    {
        $this->expectExceptionObject(new SheetNotFoundException('unknown sheet'));
        $this->getFlatFileIterator(sheetName: 'unknown sheet');
    }

    /**
     * @test
     */
    public function it_return_an_empty_array_when_file_is_empty(): void
    {
        $flatFileIterator = $this->getFlatFileIterator(sheetName: 'Empty sheet');
        $actualFileHeader = $flatFileIterator->getHeaders();
        $expectedFileHeader = FileHeaderCollection::createFromNormalized([]);
        $this->assertEquals($expectedFileHeader, $actualFileHeader);

        $actualFileContent = iterator_to_array($flatFileIterator);
        $expectedFileContent = [];

        $this->assertEquals($expectedFileContent, $actualFileContent);
    }

    private function getFlatFileIterator(
        string $filePath = 'components/tailored-import/back/tests/Common/simple_import.xlsx',
        int $headerLine = 1,
        int $firstColumn = 0,
        int $productLine = 2,
        string $sheetName = 'Products'
    ): XlsxFileIterator {
        $fileStructure = FileStructure::createFromNormalized([
            'header_row' => $headerLine,
            'first_column' => $firstColumn,
            'first_product_row' => $productLine,
            'sheet_name' => $sheetName,
        ]);

        return $this->get('akeneo.tailored_import.spout.file_reader.factory')->create('xlsx', $filePath, $fileStructure);
    }
}
