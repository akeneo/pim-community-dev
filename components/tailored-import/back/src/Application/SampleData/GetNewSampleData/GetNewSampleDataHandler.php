<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetNewSampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData\GetSampleDataResult;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleDataInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNewSampleDataHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        private ReplaceSampleDataInterface $replaceSampleData
    ) {
    }

    public function handle(GetNewSampleDataQuery $getNewSampleDataQuery): GetSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getNewSampleDataQuery->fileKey);

        $extractedColumn = $fileReader->readColumnValues(
            $getNewSampleDataQuery->sheetName,
            $getNewSampleDataQuery->productLine,
            intval($getNewSampleDataQuery->columnIndex)
        );

        $sampleData = $this->replaceSampleData->fromExtractedColumn($extractedColumn, $getNewSampleDataQuery->currentSample, $getNewSampleDataQuery->indexToChange);

        return GetSampleDataResult::create($sampleData);
    }
}