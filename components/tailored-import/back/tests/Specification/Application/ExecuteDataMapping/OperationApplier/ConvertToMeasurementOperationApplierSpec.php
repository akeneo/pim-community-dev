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
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ConvertToMeasurementOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_convert_to_measurement_operation(): void
    {
        $this->supports(new ConvertToMeasurementOperation($this->uuid, ',', 'GRAM'))->shouldReturn(true);
    }

    public function it_applies_convert_to_measurement_operation(): void
    {
        $operation = new ConvertToMeasurementOperation($this->uuid, ',', 'GRAM');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new MeasurementValue('1.234', 'GRAM'));
        $this->applyOperation($operation, new StringValue('12,34'))
            ->shouldBeLike(new MeasurementValue('12.34', 'GRAM'));
        $this->applyOperation($operation, new StringValue('123,4'))
            ->shouldBeLike(new MeasurementValue('123.4', 'GRAM'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new MeasurementValue('1234', 'GRAM'));
        $this->applyOperation($operation, new StringValue('-1234'))
            ->shouldBeLike(new MeasurementValue('-1234', 'GRAM'));

        $operation = new ConvertToMeasurementOperation($this->uuid, '.', 'GRAM');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new MeasurementValue('1.234', 'GRAM'));
        $this->applyOperation($operation, new StringValue('12.34'))
            ->shouldBeLike(new MeasurementValue('12.34', 'GRAM'));
        $this->applyOperation($operation, new StringValue('123.4'))
            ->shouldBeLike(new MeasurementValue('123.4', 'GRAM'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new MeasurementValue('1234', 'GRAM'));
        $this->applyOperation($operation, new StringValue('-12.34'))
            ->shouldBeLike(new MeasurementValue('-12.34', 'GRAM'));
    }

    public function it_does_not_return_an_invalid_value_when_using_default_separator(): void
    {
        $operation = new ConvertToMeasurementOperation($this->uuid, ',', 'GRAM');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new MeasurementValue('1.234', 'GRAM'));
        $this->applyOperation($operation, new StringValue('12.34'))
            ->shouldBeLike(new MeasurementValue('12.34', 'GRAM'));
        $this->applyOperation($operation, new StringValue('123.4'))
            ->shouldBeLike(new MeasurementValue('123.4', 'GRAM'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new MeasurementValue('1234', 'GRAM'));
    }

    public function it_returns_an_invalid_value_when_cannot_convert_to_number(): void
    {
        $operation = new ConvertToMeasurementOperation($this->uuid, '.', 'GRAM');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1,234" to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1234,'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1234," to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1..234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1..234" to a number with separator "."'));

        $operation = new ConvertToMeasurementOperation($this->uuid, ',', 'GRAM');

        $this->applyOperation($operation, new StringValue('c1234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "c1234" to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1,,234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1,,234" to a number with separator ","'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ConvertToMeasurementOperation($this->uuid, '.', 'GRAM');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, ConvertToMeasurementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, ConvertToMeasurementOperation::class, ConvertToMeasurementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
