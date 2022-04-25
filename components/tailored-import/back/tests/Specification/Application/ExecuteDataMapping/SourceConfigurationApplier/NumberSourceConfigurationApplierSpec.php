<?php


namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\NumberSourceConfiguration;
use PhpSpec\ObjectBehavior;

class NumberSourceConfigurationApplierSpec extends ObjectBehavior
{
    public function it_replaces_decimal_separator_in_the_value(NumberSourceConfiguration $sourceConfiguration)
    {
        $sourceConfiguration->getDecimalSeparator()->willReturn(',');

        $this->applySourceConfiguration($sourceConfiguration, '40,5')
            ->shouldReturn('40.5');
    }

    public function it_supports_number_source_configuration(
        MeasurementSourceConfiguration $measurementSourceConfiguration,
        NumberSourceConfiguration $numberSourceConfiguration
    ) {
        $this->supports($measurementSourceConfiguration, '69')->shouldReturn(false);
        $this->supports($numberSourceConfiguration, '69')->shouldReturn(true);
    }

    public function it_throws_an_error_if_it_is_not_a_number_source_configuration(MeasurementSourceConfiguration $measurementSourceConfiguration)
    {
        $this->shouldThrow(new \InvalidArgumentException('Cannot apply Number source configuration on this value'))
            ->during('applySourceConfiguration', [$measurementSourceConfiguration, '69']);
    }

    public function it_throws_an_error_if_decimal_separator_in_value_is_working_but_not_expected(NumberSourceConfiguration $sourceConfiguration)
    {
        $sourceConfiguration->getDecimalSeparator()->willReturn(',');

        $this->shouldThrow(new \InvalidArgumentException('Unexpected valid decimal separator "." on this value'))
            ->during('applySourceConfiguration', [$sourceConfiguration, '23.9']);
    }
}
