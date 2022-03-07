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

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\CellsFormatter;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileIterator;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileReader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileIteratorFactorySpec extends ObjectBehavior
{
    public function let(CellsFormatter $cellsFormatter)
    {
        $this->beConstructedWith($cellsFormatter);
    }

    public function it_create_an_xlsx_file_iterator(CellsFormatter $cellsFormatter)
    {
        $cellsFormatter->formatCell(Argument::any())->willReturn('placeholder');

        $filePath = 'components/tailored-import/back/tests/Common/simple_import.xlsx';
        $fileStructure = FileStructure::createFromNormalized([
            'header_line' => 1,
            'first_column' => 0,
            'product_line' => 2,
            'sheet_name' => 'Products',
        ]);

        $this->create('xlsx', $filePath, $fileStructure)->shouldBeAnInstanceOf(XlsxFileIterator::class);
    }

    public function it_throw_an_exception_when_file_type_is_unsupported()
    {
        $filePath = 'path-to-xlsx-file.ods';
        $fileStructure = FileStructure::createFromNormalized([
            'header_line' => 1,
            'first_column' => 0,
            'product_line' => 2,
            'sheet_name' => 'Products',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['ods', $filePath, $fileStructure]);
    }
}
