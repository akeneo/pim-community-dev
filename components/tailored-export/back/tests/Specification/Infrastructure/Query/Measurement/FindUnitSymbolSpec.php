<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement\FindUnitSymbol;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit;
use PhpSpec\ObjectBehavior;

class FindUnitSymbolSpec extends ObjectBehavior
{
    public function let(
        GetUnit $getUnit
    ): void {
        $this->beConstructedWith($getUnit);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindUnitSymbol::class);
    }

    public function it_finds_unit_symbol(
        GetUnit $getUnit
    ): void {
        $measurementFamilyCode = 'Weight';
        $unitCode = 'GRAM';

        $expectedUnit = new Unit();
        $expectedUnit->code = $unitCode;
        $expectedUnit->symbol = 'g';

        $getUnit->byMeasurementFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode)
            ->willReturn($expectedUnit);

        $this->byFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode)->shouldReturn($expectedUnit->symbol);
    }

    public function it_returns_null_if_unit_is_empty(
        GetUnit $getUnit
    ): void {
        $measurementFamilyCode = 'Weight';
        $unitCode = 'GRAM';

        $expectedUnit = new Unit();
        $expectedUnit->code = $unitCode;

        $getUnit->byMeasurementFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode)
            ->willReturn($expectedUnit);

        $this->byFamilyCodeAndUnitCode($measurementFamilyCode, $unitCode)->shouldReturn(null);
    }
}
