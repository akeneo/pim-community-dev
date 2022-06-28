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
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\CleanHTMLTagsOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CleanHTMLTagsOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_clean_html_tags_operation(): void
    {
        $this->supports(new CleanHTMLTagsOperation($this->uuid))->shouldReturn(true);
    }

    public function it_applies_clean_html_tags_operation(): void
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation($this->uuid);
        $simpleStringValueContainingHtml = new StringValue('<h1>test -&gt;&nbsp;&quot;</h1>');
        $complexStringValueContainingHtml = new StringValue('<h1>My description</h1><p>Lorem picsouuuuuuuuuuuuuuuuuuuu</p><ul><li>Item 1</li><li>Item 2</li><li><a href="https://akeneo.com">Link item</a></li></ul>');

        $this->applyOperation($cleanHTMLTagsOperation, $simpleStringValueContainingHtml)
            ->shouldBeLike(new StringValue('test -> "'));
        $this->applyOperation($cleanHTMLTagsOperation, $complexStringValueContainingHtml)
            ->shouldBeLike(new StringValue('My descriptionLorem picsouuuuuuuuuuuuuuuuuuuuItem 1Item 2Link item'));
    }

    public function it_does_nothing_when_value_does_not_contain_html_tags(): void
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('test');

        $this->applyOperation($cleanHTMLTagsOperation, $value)->shouldBeLike($value);
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new NumberValue('18');

        $this->shouldThrow(new UnexpectedValueException($value, StringValue::class, CleanHTMLTagsOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new BooleanReplacementOperation($this->uuid, ['true' => ['1'], 'false' => ['0']]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, CleanHTMLTagsOperation::class, CleanHTMLTagsOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
