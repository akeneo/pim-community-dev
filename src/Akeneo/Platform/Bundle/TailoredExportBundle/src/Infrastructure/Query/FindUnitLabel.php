<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

class FindUnitLabel implements FindUnitLabelInterface
{
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(GetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode, string $locale): ?string
    {
        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $familyCode,
            $locale
        );

        return $unitTranslations[$unitCode] ?? null;
    }
}
