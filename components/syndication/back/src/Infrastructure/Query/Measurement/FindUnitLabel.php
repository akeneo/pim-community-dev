<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Measurement;

use Akeneo\Platform\Syndication\Domain\Query\FindUnitLabelInterface;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnitTranslations;

class FindUnitLabel implements FindUnitLabelInterface
{
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(GetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    /**
     * @inheritDoc
     */
    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode, string $locale): ?string
    {
        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $familyCode,
            $locale
        );

        return $unitTranslations[$unitCode] ?? null;
    }
}
