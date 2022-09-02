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

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\ReplaceSampleData;

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
