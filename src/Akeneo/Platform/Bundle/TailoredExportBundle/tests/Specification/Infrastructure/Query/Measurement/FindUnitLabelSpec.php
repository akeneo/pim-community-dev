<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement\FindUnitLabel;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use PhpSpec\ObjectBehavior;

class FindUnitLabelSpec extends ObjectBehavior
{
    public function let(
        GetUnitTranslations $getUnitTranslations
    ): void {
        $this->beConstructedWith($getUnitTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindUnitLabel::class);
    }

    public function it_finds_unit_label(
        GetUnitTranslations $getUnitTranslations
    ): void {
        $measurementFamilyCode = 'weight';
        $unitCode = 'GRAM';
        $localeCode = 'fr_FR';

        $expectedLabel = 'Grames';
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale($measurementFamilyCode, $localeCode)
            ->willReturn([$unitCode => $expectedLabel]);

        $this->byFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode, $localeCode)->shouldReturn($expectedLabel);
    }

    public function it_returns_null_if_label_is_empty(
        GetUnitTranslations $getUnitTranslations
    ): void {
        $measurementFamilyCode = 'weight';
        $unitCode = 'GRAM';
        $localeCode = 'fr_FR';

        $getUnitTranslations->byMeasurementFamilyCodeAndLocale($measurementFamilyCode, $localeCode)
            ->willReturn([]);

        $this->byFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode, $localeCode)->shouldReturn(null);
    }
}
