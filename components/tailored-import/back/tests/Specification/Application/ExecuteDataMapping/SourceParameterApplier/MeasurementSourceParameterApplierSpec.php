<?php

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use PhpSpec\ObjectBehavior;

class MeasurementSourceParameterApplierSpec extends ObjectBehavior
{
    public function it_replaces_decimal_separator_in_the_value(MeasurementSourceParameter $sourceParameter)
    {
        $sourceParameter->getDecimalSeparator()->willReturn(',');

        $this->applySourceParameter($sourceParameter, '40,5')
            ->shouldReturn('40.5');
    }

    public function it_supports_measurement_source_parameter(
        MeasurementSourceParameter $sourceParameter,
        NumberSourceParameter $numberSourceParameter
    ) {
        $this->supports($sourceParameter, '69')->shouldReturn(true);
        $this->supports($numberSourceParameter, '69')->shouldReturn(false);
    }

    public function it_throws_an_error_if_it_is_not_a_measurement_source_parameter(NumberSourceParameter $numberSourceParameter)
    {
        $this->shouldThrow(new \InvalidArgumentException('Cannot apply Measurement source parameter on this value'))
            ->during('applySourceParameter', [$numberSourceParameter, '69']);
    }

    public function it_throws_an_error_if_decimal_separator_in_value_is_working_but_not_expected(MeasurementSourceParameter $sourceParameter)
    {
        $sourceParameter->getDecimalSeparator()->willReturn(',');

        $this->shouldThrow(new \InvalidArgumentException('Unexpected valid decimal separator "." on this value'))
            ->during('applySourceParameter', [$sourceParameter, '23.9']);
    }
}
