<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\GetSampleData\SelectSampleDataInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\GetJobConfigurationInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        private GetJobConfigurationInterface $getJobConfiguration,
        private SelectSampleDataInterface $selectSampleData
    ) {
    }

    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $jobConfiguration = $this->getJobConfiguration->byJobCode($getSampleDataQuery->jobCode);
        $fileReader = $this->xlsxFileReaderFactory->create($jobConfiguration->getFileKey());

        $extractedColumn = $fileReader->readColumnValues(
            $jobConfiguration->getFileStructure()->getSheetName(),
            $jobConfiguration->getFileStructure()->getProductLine(),
            intval($getSampleDataQuery->columnIndex)
        );

        $sampleData = $this->selectSampleData->fromExtractedColumn($extractedColumn);

        return GetSampleDataResult::create($sampleData);
    }
}
