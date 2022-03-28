<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Application\GetSampleData;

use Akeneo\Platform\TailoredImport\Application\GetSampleData\GetSampleDataQuery;
use Akeneo\Platform\TailoredImport\Domain\GetSampleData\SelectSampleDataInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\JobConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\GetJobConfigurationInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetSampleDataHandlerSpec extends ObjectBehavior
{
    public function it_return_a_sample_of_data(
        XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        SelectSampleDataInterface $selectSampleData,
        JobConfiguration $jobConfiguration,
        XlsxFileReaderInterface $fileReader
    ) {
        $query = new GetSampleDataQuery();
        $query->columnIndex = "1";
        $query->sheetName = "sheet1";
        $query->fileKey = "/filepath";
        $query->productLine = 2;

        $xlsxFileReaderFactory->create($query->fileKey)->willReturn($fileReader)->shouldBeCalled();
        $fileReader->readColumnValues(
            $query->sheetName,
            $query->productLine,
            intval($query->columnIndex)
        )->willReturn(["value1","value1","value2","value2","value3","value3"])->shouldBeCalled();
        $selectSampleData->fromExtractedColumn(["value1","value1","value2","value2","value3","value3"])->willReturn(["value1","value2","value3"])->shouldBeCalled();

        $this->beConstructedWith(
            $xlsxFileReaderFactory,
            $selectSampleData
        );

        $this->shouldNotThrow()->during('handle', [$query]);
    }
}
