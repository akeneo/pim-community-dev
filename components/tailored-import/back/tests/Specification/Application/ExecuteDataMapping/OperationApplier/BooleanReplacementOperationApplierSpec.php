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

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\NoMappedValueFound;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\BooleanReplacementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class BooleanReplacementOperationApplierSpec extends ObjectBehavior
{
    private $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_boolean_replacement_operation(): void
    {
        $this->supports(new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]))->shouldReturn(true);
    }

    public function it_applies_boolean_replacement_operation(): void
    {
        $booleanReplacementOperation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $falseValue = new StringValue('0');
        $trueValue = new StringValue('1');

        $this->applyOperation($booleanReplacementOperation, $falseValue)
            ->shouldBeLike(new BooleanValue(false));
        $this->applyOperation($booleanReplacementOperation, $trueValue)
            ->shouldBeLike(new BooleanValue(true));
    }

    public function it_throws_an_exception_when_the_value_is_not_mapped(): void
    {
        $booleanReplacementOperation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $unmappedValue = new StringValue('something');

        $this->shouldThrow(new NoMappedValueFound($unmappedValue->getValue()))
            ->during('applyOperation', [$booleanReplacementOperation, $unmappedValue]);
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, BooleanReplacementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, BooleanReplacementOperation::class, BooleanReplacementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
