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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\ChangeCaseOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

final class ChangeCaseOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_change_case_operation(): void
    {
        $this->supports(new ChangeCaseOperation(
            $this->uuid,
            ChangeCaseOperation::MODE_UPPERCASE
        ))->shouldReturn(true);
    }

    public function it_applies_change_case_to_uppercase_operation(): void
    {
        $operation = new ChangeCaseOperation($this->uuid, ChangeCaseOperation::MODE_UPPERCASE);
        $result = $this->applyOperation($operation, New StringValue('i m a text éà'));
        $result->getValue()->shouldReturn('I M A TEXT ÉÀ');
    }

    public function it_applies_change_case_to_lowercase_operation(): void
    {
        $operation = new ChangeCaseOperation($this->uuid, ChangeCaseOperation::MODE_LOWERCASE);
        $result = $this->applyOperation($operation, New StringValue('I M a TeXt Éà'));
        $result->getValue()->shouldReturn('i m a text éà');
    }

    public function it_applies_change_case_to_capitalize_operation(): void
    {
        $operation = new ChangeCaseOperation($this->uuid, ChangeCaseOperation::MODE_CAPITALIZE);
        $result = $this->applyOperation($operation, New StringValue('i M A teXT éÀ'));
        $result->getValue()->shouldReturn('I m a text éà');
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new ChangeCaseOperation(
            $this->uuid,
            ChangeCaseOperation::MODE_CAPITALIZE
        );

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLOperation($this->uuid, [CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException(
            $operation,
            ChangeCaseOperation::class,
            ChangeCaseOperationApplier::class,
        ))->during('applyOperation', [$operation, $value]);
    }
}
