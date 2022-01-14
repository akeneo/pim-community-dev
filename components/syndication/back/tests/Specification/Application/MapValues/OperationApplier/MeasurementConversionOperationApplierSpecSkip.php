<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\Syndication\Domain\Query\MeasurementConverterInterface;
use PhpSpec\ObjectBehavior;

class MeasurementConversionOperationApplierSpec extends ObjectBehavior
{
    public function let(
        MeasurementConverterInterface $measurementConverter
    ) {
        $this->beConstructedWith($measurementConverter);
    }

    public function it_supports_measurement_conversion_operation_and_measurement_value()
    {
        $operation = new MeasurementConversionOperation('Weight', 'GRAM');
        $value = new MeasurementValue('10.4', 'KILOGRAM');

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_measurement_conversion_operation(
        MeasurementConverterInterface $measurementConverter
    ) {
        $operation = new MeasurementConversionOperation('Weight', 'GRAM');
        $value = new MeasurementValue('10.4', 'KILOGRAM');

        $measurementConverter->convert(
            $operation->getMeasurementFamilyCode(),
            $value->getUnitCode(),
            $operation->getTargetUnitCode(),
            $value->getValue(),
        )->willReturn('10400');

        $this->applyOperation($operation, $value)->shouldBeLike(new MeasurementValue('10400', 'GRAM'));
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
