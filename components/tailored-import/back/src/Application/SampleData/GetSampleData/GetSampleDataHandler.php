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

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;

final class GetSampleDataHandler
{
    private const MAX_SAMPLE_DATA_SIZE = 1000;

    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getSampleDataQuery->fileKey);

        $extractedColumns = $fileReader->readColumnsValues(
            $getSampleDataQuery->sheetName,
            $getSampleDataQuery->productLine,
            $getSampleDataQuery->columnIndices,
            self::MAX_SAMPLE_DATA_SIZE,
        );

        $sampleData = SelectSampleData::fromExtractedColumns($extractedColumns);

        return GetSampleDataResult::create($sampleData);
    }
}
