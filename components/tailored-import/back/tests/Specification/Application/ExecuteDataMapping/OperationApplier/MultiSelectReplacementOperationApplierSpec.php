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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\MultiSelectReplacementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class MultiSelectReplacementOperationApplierSpec extends ObjectBehavior
{
    private $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_multi_select_replacement_operation(): void
    {
        $this->supports(new MultiSelectReplacementOperation($this->uuid, [
            'adidas' => ['nike', 'reebok'],
            'foo' => ['bar', 'baz'],
        ]))->shouldReturn(true);
    }

    public function it_applies_multi_select_replacement_operation(): void
    {
        $operation = new MultiSelectReplacementOperation($this->uuid, [
            'adidas' => ['nike', 'reebok'],
            'foo' => ['bar', 'baz'],
        ]);

        $this->applyOperation($operation, new StringValue('nike'))->shouldBeLike(new StringValue('adidas'));
        $this->applyOperation($operation, new ArrayValue(['nike', 'baz']))->shouldBeLike(new ArrayValue(['adidas', 'foo']));
    }

    public function it_returns_the_original_value_when_the_value_is_not_mapped(): void
    {
        $operation = new MultiSelectReplacementOperation($this->uuid, [
            'adidas' => ['nike', 'reebok'],
            'foo' => ['bar', 'baz'],
        ]);

        $this->applyOperation($operation, new StringValue('empty'))->shouldBeLike(new StringValue('empty'));
        $this->applyOperation($operation, new ArrayValue(['empty', 'other']))->shouldBeLike(new ArrayValue(['empty', 'other']));
        $this->applyOperation($operation, new ArrayValue(['nike', 'other']))->shouldBeLike(new ArrayValue(['adidas', 'other']));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new MultiSelectReplacementOperation($this->uuid, [
            'adidas' => ['nike', 'reebok'],
            'foo' => ['bar', 'baz'],
        ]);

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException(
            $operation,
            MultiSelectReplacementOperation::class,
            MultiSelectReplacementOperationApplier::class,
        ))->during('applyOperation', [$operation, $value]);
    }
}
