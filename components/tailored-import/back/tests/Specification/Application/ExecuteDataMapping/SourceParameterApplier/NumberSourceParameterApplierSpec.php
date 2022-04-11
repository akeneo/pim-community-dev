<?php


namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use PhpSpec\ObjectBehavior;

class NumberSourceParameterApplierSpec extends ObjectBehavior
{
    public function it_replaces_decimal_separator_in_the_value(NumberSourceParameter $sourceParameter)
    {
        $sourceParameter->getDecimalSeparator()->willReturn(',');

        $this->applySourceParameter($sourceParameter, '40,5')
            ->shouldReturn('40.5');
    }

    public function it_supports_number_source_parameter(
        MeasurementSourceParameter $measurementSourceParameter,
        NumberSourceParameter $numberSourceParameter
    ) {
        $this->supports($measurementSourceParameter, '69')->shouldReturn(false);
        $this->supports($numberSourceParameter, '69')->shouldReturn(true);
    }

    public function it_throws_an_error_if_it_is_not_a_number_source_parameter(MeasurementSourceParameter $measurementSourceParameter)
    {
        $this->shouldThrow(new \InvalidArgumentException('Cannot apply Number source parameter on this value'))
            ->during('applySourceParameter', [$measurementSourceParameter, '69']);
    }

    public function it_throws_an_error_if_decimal_separator_in_value_is_working_but_not_expected(NumberSourceParameter $sourceParameter)
    {
        $sourceParameter->getDecimalSeparator()->willReturn(',');

        $this->shouldThrow(new \InvalidArgumentException('Unexpected valid decimal separator "." on this value'))
            ->during('applySourceParameter', [$sourceParameter, '23.9']);
    }
}
