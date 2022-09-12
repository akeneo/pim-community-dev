<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\RemoveWhitespaceOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

final class RemoveWhitespaceOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_remove_whitespace_operation(): void
    {
        $this->supports(new RemoveWhitespaceOperation(
            $this->uuid,
            [
                RemoveWhitespaceOperation::MODE_TRIM,
            ],
        ))->shouldReturn(true);
    }

    public function it_applies_remove_whitespace_operation_with_consecutive_mode(): void
    {
        $operation = new RemoveWhitespaceOperation($this->uuid, [RemoveWhitespaceOperation::MODE_CONSECUTIVE]);
        $result = $this->applyOperation($operation, New StringValue(' Hello  I am a  text  '));
        $result->getValue()->shouldReturn(' Hello I am a text ');
    }

    public function it_applies_remove_whitespace_operation_with_trim_mode(): void
    {
        $operation = new RemoveWhitespaceOperation($this->uuid, [RemoveWhitespaceOperation::MODE_TRIM]);
        $result = $this->applyOperation($operation, New StringValue(' Hello  I am a  text  '));
        $result->getValue()->shouldReturn('Hello  I am a  text');
    }

    public function it_applies_remove_whitespace_operation_with_all_modes(): void
    {
        $operation = new RemoveWhitespaceOperation($this->uuid, [
            RemoveWhitespaceOperation::MODE_CONSECUTIVE,
            RemoveWhitespaceOperation::MODE_TRIM,
        ]);
        $result = $this->applyOperation($operation, New StringValue(' Hello  I am a  text  '));
        $result->getValue()->shouldReturn('Hello I am a text');
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new RemoveWhitespaceOperation(
            $this->uuid,
            [RemoveWhitespaceOperation::MODE_CONSECUTIVE]
        );

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException(
            $operation,
            RemoveWhitespaceOperation::class,
            RemoveWhitespaceOperationApplier::class,
        ))->during('applyOperation', [$operation, $value]);
    }
}
