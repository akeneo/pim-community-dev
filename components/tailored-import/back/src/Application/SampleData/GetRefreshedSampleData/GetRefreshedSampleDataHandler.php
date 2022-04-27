<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRefreshedSampleDataHandler
{
    private const MAX_SAMPLE_DATA_SIZE = 1000;

    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetRefreshedSampleDataQuery $getRefreshedSampleDataQuery): GetRefreshedSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getRefreshedSampleDataQuery->fileKey);

        $extractedColumns = $fileReader->readColumnsValues(
            $getRefreshedSampleDataQuery->sheetName,
            $getRefreshedSampleDataQuery->productLine,
            $getRefreshedSampleDataQuery->columnIndices,
            self::MAX_SAMPLE_DATA_SIZE,
        );

        $sampleData = ReplaceSampleData::fromExtractedColumns($extractedColumns, $getRefreshedSampleDataQuery->currentSample);

        return GetRefreshedSampleDataResult::create($sampleData);
    }
}
