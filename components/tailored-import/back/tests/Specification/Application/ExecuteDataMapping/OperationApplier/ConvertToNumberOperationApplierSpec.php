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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\ConvertToNumberOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToNumberOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ConvertToNumberOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_convert_to_number_operation(): void
    {
        $this->supports(new ConvertToNumberOperation($this->uuid, ','))->shouldReturn(true);
    }

    public function it_applies_convert_to_number_operation(): void
    {
        $operation = new ConvertToNumberOperation($this->uuid, ',');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new NumberValue('1.234'));
        $this->applyOperation($operation, new StringValue('12,34'))
            ->shouldBeLike(new NumberValue('12.34'));
        $this->applyOperation($operation, new StringValue('123,4'))
            ->shouldBeLike(new NumberValue('123.4'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new NumberValue('1234'));

        $operation = new ConvertToNumberOperation($this->uuid, '.');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new NumberValue('1.234'));
        $this->applyOperation($operation, new StringValue('12.34'))
            ->shouldBeLike(new NumberValue('12.34'));
        $this->applyOperation($operation, new StringValue('123.4'))
            ->shouldBeLike(new NumberValue('123.4'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new NumberValue('1234'));
    }

    public function it_returns_an_invalid_value_when_cannot_convert_to_number(): void
    {
        $operation = new ConvertToNumberOperation($this->uuid, '.');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1,234" to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1234,'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1234," to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator "."'));

        $operation = new ConvertToNumberOperation($this->uuid, ',');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1.234" to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1234.'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1234." to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator ","'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ConvertToNumberOperation($this->uuid, '.');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, ConvertToNumberOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, ConvertToNumberOperation::class, ConvertToNumberOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
