<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceSampleData
{
    private SelectSampleData $selectSampleData;

    public function __construct()
    {
        $this->selectSampleData = new SelectSampleData();
    }

    public function fromExtractedColumn(array $extractedColumn, array $currentSample, int $indexToReplace): array
    {
        $diff = array_diff($extractedColumn, $currentSample);
        $currentSample[$indexToReplace] = current($this->selectSampleData->fromExtractedColumn($diff, 1));

        return array_pad($currentSample, 3, null);
    }
}
