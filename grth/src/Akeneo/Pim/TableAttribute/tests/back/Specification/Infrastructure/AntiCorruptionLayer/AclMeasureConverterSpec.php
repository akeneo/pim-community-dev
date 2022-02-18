<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Measurement\MeasureConverter;
use Akeneo\Pim\TableAttribute\Domain\Value\Measurement\MeasurementUnitNotFoundException;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\AclMeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter as BaseMeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use PhpSpec\ObjectBehavior;

class AclMeasureConverterSpec extends ObjectBehavior
{
    function let(BaseMeasureConverter $measureConverter)
    {
        $this->beConstructedWith($measureConverter);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(MeasureConverter::class);
        $this->shouldHaveType(AclMeasureConverter::class);
    }

    function it_throws_an_exception_when_amount_is_not_a_numeric(MeasureConverter $measureConverter)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('convertAmountInStandardUnit', [
            MeasurementFamilyCode::fromString('duration'),
            'foo',
            'day',
        ]);
    }

    function it_throws_an_exception_when_unit_is_not_found(BaseMeasureConverter $measureConverter)
    {
        $measureConverter->setFamily('duration')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('meter', '10.5')->shouldBeCalled()->willThrow(
            new UnitNotFoundException()
        );

        $this->shouldThrow(MeasurementUnitNotFoundException::forUnit('meter', 'duration'))->during(
            'convertAmountInStandardUnit',
            [MeasurementFamilyCode::fromString('duration'), '10.5', 'meter']
        );
    }

    function it_converts_amount_in_standard_unit(MeasureConverter $measureConverter)
    {
        $measureConverter->setFamily('duration')->shouldBeCalledOnce();
        $measureConverter->convertBaseToStandard('day', '1')->shouldBeCalledOnce()->willReturn('86400');
        $this->convertAmountInStandardUnit(MeasurementFamilyCode::fromString('duration'), '1', 'day')
             ->shouldReturn('86400');
    }
}
