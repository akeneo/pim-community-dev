<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\SimpleReferenceEntityReplacementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleReferenceEntityReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

final class SimpleReferenceEntityReplacementOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_simple_reference_entity_replacement_operation(): void
    {
        $this->supports(new SimpleReferenceEntityReplacementOperation(
            $this->uuid,
            [
                'adidas' => ['nike'],
            ],
        ))->shouldReturn(true);
    }

    public function it_applies_simple_reference_entity_replacement_replacement_operation(): void
    {
        $operation = new SimpleReferenceEntityReplacementOperation(
            $this->uuid,
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
                'int' => ['8'],
            ],
        );

        $this->applyOperation($operation, new StringValue('nike'))->shouldBeLike(new StringValue('adidas'));
        $this->applyOperation($operation, new StringValue('8'))->shouldBeLike(new StringValue('int'));
    }

    public function it_returns_the_original_value_when_the_value_is_not_mapped(): void
    {
        $operation = new SimpleReferenceEntityReplacementOperation(
            $this->uuid,
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        );

        $this->applyOperation($operation, new StringValue('empty'))->shouldBeLike(new StringValue('empty'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new SimpleReferenceEntityReplacementOperation(
            $this->uuid,
            [
                'adidas' => ['nike', 'reebok'],
                'foo' => ['bar', 'baz'],
            ],
        );

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLOperation($this->uuid, [CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, SimpleReferenceEntityReplacementOperation::class, SimpleReferenceEntityReplacementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
