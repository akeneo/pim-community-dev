<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Syndication\Infrastructure\Query\Measurement;

use Akeneo\Platform\Syndication\Infrastructure\Query\Measurement\MeasurementConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;

class MeasurementConverterSpec extends ObjectBehavior
{
    public function let(
        MeasureConverter $measureConverter
    ): void {
        $this->beConstructedWith($measureConverter);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MeasurementConverter::class);
    }

    public function it_converts_units(
        MeasureConverter $measureConverter
    ): void {
        $measurementFamilyCode = 'Weight';
        $currentUnitCode = 'KILOGRAM';
        $targetUnitCode = 'GRAM';
        $value = '10';

        $measureConverter->convert($currentUnitCode, $targetUnitCode, $value)->willReturn('10000');
        $measureConverter->setFamily($measurementFamilyCode)->shouldBeCalled();

        $this->convert($measurementFamilyCode, $currentUnitCode, $targetUnitCode, $value)->shouldReturn('10000');
    }
}
