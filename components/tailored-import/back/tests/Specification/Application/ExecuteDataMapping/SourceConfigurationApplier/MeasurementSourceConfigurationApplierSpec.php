<?php

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\NumberSourceConfiguration;
use PhpSpec\ObjectBehavior;

class MeasurementSourceConfigurationApplierSpec extends ObjectBehavior
{
    public function it_replaces_decimal_separator_in_the_value(MeasurementSourceConfiguration $sourceConfiguration)
    {
        $sourceConfiguration->getDecimalSeparator()->willReturn(',');

        $this->applySourceConfiguration($sourceConfiguration, '40,5')
            ->shouldReturn('40.5');
    }

    public function it_supports_measurement_source_configuration(
        MeasurementSourceConfiguration $sourceConfiguration,
        NumberSourceConfiguration $numberSourceConfiguration
    ) {
        $this->supports($sourceConfiguration, '69')->shouldReturn(true);
        $this->supports($numberSourceConfiguration, '69')->shouldReturn(false);
    }

    public function it_throws_an_error_if_it_is_not_a_measurement_source_configuration(NumberSourceConfiguration $numberSourceConfiguration)
    {
        $this->shouldThrow(new \InvalidArgumentException('Cannot apply Measurement source configuration on this value'))
            ->during('applySourceConfiguration', [$numberSourceConfiguration, '69']);
    }

    public function it_throws_an_error_if_decimal_separator_in_value_is_working_but_not_expected(MeasurementSourceConfiguration $sourceConfiguration)
    {
        $sourceConfiguration->getDecimalSeparator()->willReturn(',');

        $this->shouldThrow(new \InvalidArgumentException('Unexpected valid decimal separator "." on this value'))
            ->during('applySourceConfiguration', [$sourceConfiguration, '23.9']);
    }
}
