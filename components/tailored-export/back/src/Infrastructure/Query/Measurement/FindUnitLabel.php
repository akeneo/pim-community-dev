<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnitTranslations;

class FindUnitLabel implements FindUnitLabelInterface
{
    public function __construct(
        private GetUnitTranslations $getUnitTranslations,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode, string $locale): ?string
    {
        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $familyCode,
            $locale,
        );

        return $unitTranslations[$unitCode] ?? null;
    }
}
