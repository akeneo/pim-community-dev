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

use Akeneo\Platform\TailoredImport\Infrastructure\Spout\CellsFormatter;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFlatFileIterator;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use PhpSpec\ObjectBehavior;

class FlatFileIteratorFactorySpec extends ObjectBehavior
{
    public function let(CellsFormatter $cellsFormatter)
    {
        $this->beConstructedWith($cellsFormatter);
    }

    public function it_create_an_xlsx_file_iterator()
    {
        $jobParameters = new JobParameters([
            'filePath' => 'components/tailored-import/back/tests/Common/simple_import.xlsx',
            'file_structure' => [
                'header_line' => 0,
                'first_column' => 0,
                'product_line' => 1,
                'sheet_name' => 'Sheet1',
            ]
        ]);

        $this->create('xlsx', $jobParameters)->shouldBeAnInstanceOf(XlsxFlatFileIterator::class);
    }

    public function it_throw_an_exception_when_file_type_is_unsupported()
    {
        $jobParameters = new JobParameters([
            'filePath' => 'path-to-xlsx-file.ods',
            'file_structure' => [
                'header_line' => 0,
                'first_column' => 0,
                'product_line' => 1,
                'sheet_name' => 'Sheet1',
            ]
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['ods', $jobParameters]);
    }
}
