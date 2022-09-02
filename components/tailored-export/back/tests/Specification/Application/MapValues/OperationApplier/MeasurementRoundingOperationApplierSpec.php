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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class MeasurementRoundingOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_measurement_rounding_operations_and_measurement_value(): void
    {
        $value = new MeasurementValue('10.4', 'KILOGRAM');
        $rounding = new MeasurementRoundingOperation('standard', 3);
        $roundUp = new MeasurementRoundingOperation('round_up', 3);
        $roundDown = new MeasurementRoundingOperation('round_down', 3);

        $this->supports($rounding, $value)->shouldReturn(true);
        $this->supports($roundUp, $value)->shouldReturn(true);
        $this->supports($roundDown, $value)->shouldReturn(true);
        $this->supports($roundDown, $value)->shouldReturn(true);
    }

    public function it_supports_measurement_rounding_up_operation_and_measurement_value(): void
    {
        $operation = new MeasurementRoundingOperation('round_up', 3);
        $value = new MeasurementValue('10.4', 'KILOGRAM');

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values(): void
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_measurement_rounding_standard_operation(): void
    {
        $operation = new MeasurementRoundingOperation('standard', 2);
        $value = new MeasurementValue('10.4184', 'GRAM');

        $this->applyOperation($operation, $value)->shouldBeLike(new MeasurementValue('10.42', 'GRAM'));
    }

    public function it_throws_when_operation_or_value_is_invalid(): void
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
