<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\GetSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SelectSampleData implements SelectSampleDataInterface
{
    public const NUMBER_OF_VALUES = 3;
    
    public function fromExtractedColumn(array $extractedColumn): array
    {
        $reducedValue = $this->filterUniqueValue($extractedColumn);
        $sampleData = $this->pickRandomValues($reducedValue);
        return $this->fillBlankValue($sampleData);
    }

    private function fillBlankValue(array $sampleData): array
    {
        return array_pad($sampleData, SelectSampleData::NUMBER_OF_VALUES, null);
    }
    
    private function filterUniqueValue(array $sampleData): array
    {
        return array_unique($sampleData);
    }
    
    private function pickRandomValues(array $sampleData): array
    {
        shuffle($sampleData);
        return array_slice($sampleData, 0, min(count($sampleData), SelectSampleData::NUMBER_OF_VALUES));
    }
}
