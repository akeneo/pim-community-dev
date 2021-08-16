<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;
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
        $this->beAnInstanceOf(FindUnitLabel::class);
    }

    public function it_finds_unit_label_with_a_measurement_family_code_a_unit_code_and_a_locate(
        GetUnitTranslations $getUnitTranslations
    ): void {
        $expectedLabel = 'Grames';
        $measurementFamilyCode = 'weight';
        $unitCode = 'GRAM';
        $localeCode = 'fr_FR';

        $getUnitTranslations->byMeasurementFamilyCodeAndLocale($measurementFamilyCode, $localeCode)
            ->willReturn([$unitCode => $expectedLabel]);

        $this->byFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode, $localeCode)->shouldReturn($expectedLabel);
    }

    public function it_returns_null_if_its_empty(
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
