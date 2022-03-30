<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceSampleData implements ReplaceSampleDataInterface
{
    public function __construct(
        private SelectSampleDataInterface $selectSampleData
    ) {}

    public function fromExtractedColumn(array $extractedColumn,array $currentSample,int $indexToReplace): array
    {
        $toRemove[] = $currentSample[$indexToReplace];
        $diff = array_diff($extractedColumn, $toRemove);
        $currentSample[$indexToReplace] = $this->selectSampleData->fromExtractedColumn($diff, 1)[0];
        $currentSample = array_unique($currentSample);
        return array_pad($currentSample, 3,null);
    }
}