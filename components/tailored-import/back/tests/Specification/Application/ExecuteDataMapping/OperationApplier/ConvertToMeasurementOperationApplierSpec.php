<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\ConvertToMeasurementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToMeasurementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ConvertToMeasurementOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_convert_to_measurement_operation(): void
    {
        $this->supports(new ConvertToMeasurementOperation(',', 'GRAM'))->shouldReturn(true);
    }

    public function it_applies_convert_to_measurement_operation(): void
    {
        $operation = new ConvertToMeasurementOperation(',', 'GRAM');
        $value = new StringValue('1,234');

        $this->applyOperation($operation, $value)
            ->shouldBeLike(new MeasurementValue('1.234', 'GRAM'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ConvertToMeasurementOperation('.', 'GRAM');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, ConvertToMeasurementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation([
            '1' => true,
            '0' => false,
        ]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, ConvertToMeasurementOperation::class, ConvertToMeasurementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
