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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\SimpleSelectReplacementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SimpleSelectReplacementOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_simple_select_replacement_operation(): void
    {
        $this->supports(new SimpleSelectReplacementOperation(
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        ))->shouldReturn(true);
    }

    public function it_applies_simple_select_replacement_operation(): void
    {
        $operation = new SimpleSelectReplacementOperation(
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        );

        $this->applyOperation($operation, new StringValue('nike'))->shouldBeLike(new StringValue('adidas'));
    }

    public function it_returns_the_original_value_when_the_value_is_not_mapped(): void
    {
        $operation = new SimpleSelectReplacementOperation(
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        );

        $this->applyOperation($operation, new StringValue('empty'))->shouldBeLike(new StringValue('empty'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new SimpleSelectReplacementOperation(
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        );

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation();
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, SimpleSelectReplacementOperation::class, SimpleSelectReplacementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
