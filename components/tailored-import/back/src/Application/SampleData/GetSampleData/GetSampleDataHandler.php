<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\SampleData\SelectSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getSampleDataQuery->fileKey);

        $extractedColumn = $fileReader->readColumnValues(
            $getSampleDataQuery->sheetName,
            $getSampleDataQuery->productLine,
            $getSampleDataQuery->columnIndex
        );

        $selectSampleData = new SelectSampleData();
        $sampleData = $selectSampleData->fromExtractedColumn($extractedColumn);

        return GetSampleDataResult::create($sampleData);
    }
}
