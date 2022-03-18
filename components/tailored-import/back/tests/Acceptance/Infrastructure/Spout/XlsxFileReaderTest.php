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
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\CellsFormatter;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileReader;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;

class XlsxFileReaderTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_returns_cells_at_first_sheet_and_at_a_specific_line(): void
    {
        $xlsxFileReader = $this->getFileReader();
        $actualCells = $xlsxFileReader->readRows(null, 1, 1);
        $expectedCells = [['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax']];

        $this->assertEquals($expectedCells, $actualCells);
    }

    /**
     * @test
     */
    public function it_returns_rows_at_a_specific_sheet_and_a_specific_line(): void
    {
        $xlsxFileReader = $this->getFileReader();

        $this->assertEquals(
            [['', 'Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax']],
            $xlsxFileReader->readRows('Empty lines and columns', 2, 1)
        );

        $this->assertEquals(
            [['', 'ref1', 'Produit 1', '12', 'TRUE', '3/22/2022', '14.4']],
            $xlsxFileReader->readRows('Empty lines and columns', 4, 1)
        );

        $this->assertEquals(
            [
                ['','ref2','Produit 2','13.87','FALSE','5/23/2022', ''],
                ['','ref3','Produit 3','16','TRUE','10/5/2015','19.2'],
            ],
            $xlsxFileReader->readRows('Empty lines and columns', 5, 2)
        );

        $this->assertEquals([], $xlsxFileReader->readRows('Empty lines and columns', 8, 1));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_file_is_not_found(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->getFileReader('non_existent_file.xlsx');
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_sheet_is_not_found(): void
    {
        $this->expectExceptionObject(new SheetNotFoundException('unknown sheet'));

        $fileReader = $this->getFileReader();
        $fileReader->readRows('unknown sheet', 1, 1);
    }

    /**
     * @test
     */
    public function it_returns_empty_cells_when_line_is_empty(): void
    {
        $xlsxFileReader = $this->getFileReader();
        $actualCells = $xlsxFileReader->readRows('Empty lines and columns', 1, 1);

        $this->assertEquals([], $actualCells);
    }

    /**
     * @test
     */
    public function it_returns_sheet_names_from_file(): void
    {
        $xlsxFileReader = $this->getFileReader();
        $actualSheetNames = $xlsxFileReader->getSheetNames();
        $expectedSheetNames = ['Products', 'Empty lines and columns', 'Empty sheet', 'Out of bound value'];

        $this->assertEquals($expectedSheetNames, $actualSheetNames);
    }

    private function getFileReader(
        string $filePath = __DIR__.'/../../../Common/simple_import.xlsx'
    ): XlsxFileReader {
        return new XlsxFileReader($filePath, new CellsFormatter());
    }
}
