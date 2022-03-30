<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\GetSampleData\SelectSampleDataInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
        private SelectSampleDataInterface $selectSampleData
    ) {
    }

    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getSampleDataQuery->fileKey);

        $extractedColumn = $fileReader->readColumnValues(
            $getSampleDataQuery->sheetName,
            $getSampleDataQuery->productLine,
            intval($getSampleDataQuery->columnIndex)
        );

        $sampleData = $this->selectSampleData->fromExtractedColumn($extractedColumn);

        return GetSampleDataResult::create($sampleData);
    }
}
