<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData\GetSampleDataResult;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleDataInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRefreshedSampleDataHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        private ReplaceSampleDataInterface $replaceSampleData
    ) {
    }

    public function handle(GetRefreshedSampleDataQuery $getRefreshedSampleDataQuery): GetSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getRefreshedSampleDataQuery->fileKey);

        $extractedColumn = $fileReader->readColumnValues(
            $getRefreshedSampleDataQuery->sheetName,
            $getRefreshedSampleDataQuery->productLine,
            $getRefreshedSampleDataQuery->columnIndex
        );

        $sampleData = $this->replaceSampleData->fromExtractedColumn($extractedColumn, $getRefreshedSampleDataQuery->currentSample, $getRefreshedSampleDataQuery->indexToChange);

        return GetSampleDataResult::create($sampleData);
    }
}
