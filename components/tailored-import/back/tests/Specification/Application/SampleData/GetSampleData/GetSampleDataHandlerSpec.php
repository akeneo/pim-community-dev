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

namespace Specification\Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData\GetSampleDataQuery;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use PhpSpec\ObjectBehavior;

class GetSampleDataHandlerSpec extends ObjectBehavior
{
    public function it_returns_a_sample_of_data(
        XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        XlsxFileReaderInterface $fileReader
    ) {
        $query = new GetSampleDataQuery();
        $query->columnIndices = [1, 2];
        $query->sheetName = 'sheet1';
        $query->fileKey = '/filepath';
        $query->productLine = 2;

        $xlsxFileReaderFactory->create($query->fileKey)->willReturn($fileReader)->shouldBeCalled();
        $fileReader->readColumnsValues(
            $query->sheetName,
            $query->productLine,
            $query->columnIndices,
            1000
        )->willReturn([
            1 => ['value1', 'value1', 'value2'],
            2 => ['value2', 'value3', 'value3'],
        ])->shouldBeCalled();

        $this->beConstructedWith($xlsxFileReaderFactory);

        $this->shouldNotThrow()->during('handle', [$query]);
    }
}
