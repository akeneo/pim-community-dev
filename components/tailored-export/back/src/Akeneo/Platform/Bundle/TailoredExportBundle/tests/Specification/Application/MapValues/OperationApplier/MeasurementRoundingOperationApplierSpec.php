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
    public function it_supports_measurement_rounding_operation_and_measurement_value()
    {
        $operation = new MeasurementRoundingOperation('up', 3);
        $value = new MeasurementValue('10.4', 'KILOGRAM');

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_measurement_rounding_standard_operation()
    {
        $operation = new MeasurementRoundingOperation('standard', 2);
        $value = new MeasurementValue('10.4184', 'GRAM');

        $this->applyOperation($operation, $value)->shouldBeLike(new MeasurementValue('10.42', 'GRAM'));
    }

    public function it_applies_measurement_rounding_up_operation()
    {
        $operation = new MeasurementRoundingOperation('up', 2);
        $value = new MeasurementValue('10.4184', 'GRAM');

        $this->applyOperation($operation, $value)->shouldBeLike(new MeasurementValue('10.42', 'GRAM'));
    }

    public function it_applies_measurement_rounding_down_operation()
    {
        $operation = new MeasurementRoundingOperation('down', 2);
        $value = new MeasurementValue('10.4184', 'GRAM');

        $this->applyOperation($operation, $value)->shouldBeLike(new MeasurementValue('10.41', 'GRAM'));
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
