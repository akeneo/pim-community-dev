<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\Model\JobConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\GetJobConfigurationInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataHandler
{
    private const SAMPLE_DATA_LENGTH = 3;
    private const SAMPLE_DATA_VALUE_MAX_LENGTH = 100;

    public function __construct(
        private GetJobConfigurationInterface $getJobConfiguration,
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $jobConfiguration = $this->getJobConfiguration->byJobCode($getSampleDataQuery->jobCode);
        $columnIndex = $this->getColumnIndex($jobConfiguration, $getSampleDataQuery->column);
        $fileReader = $this->xlsxFileReaderFactory->create($jobConfiguration->getFileKey());

        $sampleData = $fileReader->readValuesFromColumn(
            $jobConfiguration->getFileStructure()->getSheetName(),
            $jobConfiguration->getFileStructure()->getProductLine(),
            $columnIndex,
            self::SAMPLE_DATA_LENGTH
        );

        return GetSampleDataResult::create($sampleData);
    }

    private function getColumnIndex(JobConfiguration $jobConfiguration, string $columnUuid): int
    {
        $searchedColumn = array_filter(
            iterator_to_array($jobConfiguration->getColumns()->getIterator()),
            static fn (Column $column) => $column->getUuid() === $columnUuid
        );

        if (0 === count($searchedColumn)) {
            throw new \InvalidArgumentException(sprintf('Column "%s" does not exist', $columnUuid));
        }

        return array_pop($searchedColumn)->getIndex();
    }
}
