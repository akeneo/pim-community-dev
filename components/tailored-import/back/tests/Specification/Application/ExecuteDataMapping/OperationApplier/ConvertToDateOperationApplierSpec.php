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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\ConvertToDateOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToDateOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\DateValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ConvertToDateOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_convert_to_date_operation(): void
    {
        $this->supports(new ConvertToDateOperation($this->uuid, 'yyyy-mm-dd'))->shouldReturn(true);
    }

    public function it_applies_convert_to_date_operation(): void
    {
        $operation = new ConvertToDateOperation($this->uuid, 'yyyy-mm-dd');
        $value = new StringValue('2022-06-22');

        $this->applyOperation($operation, $value)
            ->shouldBeLike(new DateValue(\DateTimeImmutable::createFromFormat('Y-m-d', '2022-06-22')->setTime(0, 0)));
    }

    public function it_returns_an_invalid_value_object_when_convert_to_date_fail(): void
    {
        $operation = new ConvertToDateOperation($this->uuid, 'yyyy-mm-dd');
        $value = new StringValue('13-01-2000');

        $this->applyOperation($operation, $value)
            ->shouldBeLike(new InvalidValue('Cannot format date "13-01-2000" with provided format "yyyy-mm-dd"'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ConvertToDateOperation($this->uuid, 'yyyy-mm-dd');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, ConvertToDateOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, ConvertToDateOperation::class, ConvertToDateOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
