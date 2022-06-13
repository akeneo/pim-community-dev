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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\SplitOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SplitOperationApplierSpec extends ObjectBehavior
{
    private $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_split_operation(): void
    {
        $this->supports(new SplitOperation($this->uuid, ','))->shouldReturn(true);
    }

    public function it_applies_split_operation(): void
    {
        $operation = new SplitOperation($this->uuid, ',');
        $value = new StringValue('value1,value2, value3');

        $this->applyOperation($operation, $value)
            ->shouldBeLike(new ArrayValue(['value1', 'value2', ' value3']));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new SplitOperation($this->uuid, ',');
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, SplitOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, SplitOperation::class, SplitOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
