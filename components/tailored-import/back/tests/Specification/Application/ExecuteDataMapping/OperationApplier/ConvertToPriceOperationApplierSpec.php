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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\ConvertToPriceOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToPriceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ConvertToPriceOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_convert_to_price_operation(): void
    {
        $this->supports(new ConvertToPriceOperation($this->uuid, ',', 'EUR'))->shouldReturn(true);
    }

    public function it_applies_convert_to_price_operation(): void
    {
        $operation = new ConvertToPriceOperation($this->uuid, ',', 'EUR');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new PriceValue('1.234', 'EUR'));
        $this->applyOperation($operation, new StringValue('12,34'))
            ->shouldBeLike(new PriceValue('12.34', 'EUR'));
        $this->applyOperation($operation, new StringValue('123,4'))
            ->shouldBeLike(new PriceValue('123.4', 'EUR'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new PriceValue('1234', 'EUR'));
        $this->applyOperation($operation, new StringValue('-1234'))
            ->shouldBeLike(new PriceValue('-1234', 'EUR'));

        $operation = new ConvertToPriceOperation($this->uuid, '.', 'USD');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new PriceValue('1.234', 'USD'));
        $this->applyOperation($operation, new StringValue('12.34'))
            ->shouldBeLike(new PriceValue('12.34', 'USD'));
        $this->applyOperation($operation, new StringValue('123.4'))
            ->shouldBeLike(new PriceValue('123.4', 'USD'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new PriceValue('1234', 'USD'));
        $this->applyOperation($operation, new StringValue('-12.34'))
            ->shouldBeLike(new PriceValue('-12.34', 'USD'));
    }

    public function it_does_not_return_an_invalid_value_when_using_default_separator(): void
    {
        $operation = new ConvertToPriceOperation($this->uuid, ',', 'USD');

        $this->applyOperation($operation, new StringValue('1.234'))
            ->shouldBeLike(new PriceValue('1.234', 'USD'));
        $this->applyOperation($operation, new StringValue('12.34'))
            ->shouldBeLike(new PriceValue('12.34', 'USD'));
        $this->applyOperation($operation, new StringValue('123.4'))
            ->shouldBeLike(new PriceValue('123.4', 'USD'));
        $this->applyOperation($operation, new StringValue('1234'))
            ->shouldBeLike(new PriceValue('1234', 'USD'));
    }

    public function it_returns_an_invalid_value_when_cannot_convert_to_number(): void
    {
        $operation = new ConvertToPriceOperation($this->uuid, '.', 'USD');

        $this->applyOperation($operation, new StringValue('1,234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1,234" to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1234,'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1234," to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator "."'));
        $this->applyOperation($operation, new StringValue('1..234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1..234" to a number with separator "."'));

        $operation = new ConvertToPriceOperation($this->uuid, ',', 'USD');

        $this->applyOperation($operation, new StringValue('c1234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "c1234" to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1K234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1K234" to a number with separator ","'));
        $this->applyOperation($operation, new StringValue('1,,234'))
            ->shouldBeLike(new InvalidValue('Cannot convert "1,,234" to a number with separator ","'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ConvertToPriceOperation($this->uuid, '.', 'EUR');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, ConvertToPriceOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, ConvertToPriceOperation::class, ConvertToPriceOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
