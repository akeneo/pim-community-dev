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
        GetJobConfigurationInterface $getJobConfiguration,
        SelectSampleDataInterface $selectSampleData,
        JobConfiguration $jobConfiguration,
        XlsxFileReaderInterface $fileReader
    ) {
        $query = new GetSampleDataQuery();
        $query->jobCode = "tailoredimport";
        $query->columnIndex = "1";

        $fileStructure = FileStructure::create(1, 1, 2, "sheet1");

        $jobConfiguration->getFileKey()->willReturn("/filepath");
        $jobConfiguration->getFileStructure()->willReturn($fileStructure);

        $getJobConfiguration->byJobCode($query->jobCode)->willReturn($jobConfiguration)->shouldBeCalled();
        $xlsxFileReaderFactory->create("/filepath")->willReturn($fileReader)->shouldBeCalled();
        $fileReader->readColumnValues(
            $fileStructure->getSheetName(),
            $fileStructure->getProductLine(),
            intval($query->columnIndex)
        )->willReturn(["value1","value1","value2","value2","value3","value3"])->shouldBeCalled();
        $selectSampleData->fromExtractedColumn(["value1","value1","value2","value2","value3","value3"])->willReturn(["value1","value2","value3"])->shouldBeCalled();

        $this->beConstructedWith(
            $xlsxFileReaderFactory,
            $getJobConfiguration,
            $selectSampleData
        );

        $this->shouldNotThrow()->during('handle', [$query]);
    }
}
