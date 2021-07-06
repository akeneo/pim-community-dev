<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement;

use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

final class InMemoryGetUnitTranslations implements GetUnitTranslations
{
    private array $unitLabels = [];

    public function addUnitLabel(string $measurementFamilyCode, string $unit, string $locale, string $label)
    {
        $this->unitLabels[$measurementFamilyCode][$locale][$unit] = $label;
    }

    public function byMeasurementFamilyCodeAndLocale(string $measurementFamilyCode, string $locale): array
    {
        return $this->unitLabels[$measurementFamilyCode][$locale] ?? [];
    }
}
