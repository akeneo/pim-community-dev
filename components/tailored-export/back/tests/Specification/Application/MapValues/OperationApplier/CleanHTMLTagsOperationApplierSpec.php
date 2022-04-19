<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class CleanHTMLTagsOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_clean_html_tags_operation_and_string_value()
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();
        $stringValue = new StringValue('<h1>test</h1>');

        $this->supports($cleanHTMLTagsOperation, $stringValue)->shouldReturn(true);
    }

    public function it_does_not_support_other_operation_with_string_value()
    {
        $notSupportedDefaultValueOperation = new DefaultValueOperation('n/a');
        $stringValue = new StringValue('test');

        $this->supports($notSupportedDefaultValueOperation, $stringValue)->shouldReturn(false);
    }

    public function it_does_not_support_others_operation_and_value()
    {
        $notSupportedReplacementOperation = new ReplacementOperation([
            'true' => 'vré',
            'false' => 'fo'
        ]);

        $notSupportedBooleanValue = new BooleanValue(true);

        $this->supports($notSupportedReplacementOperation, $notSupportedBooleanValue)->shouldReturn(false);
    }

    public function it_applies_clean_html_tags_operation()
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();

        $simpleStringValueContainingHtml = new StringValue('<h1>test -&gt;&nbsp;&quot;</h1>');
        $complexStringValueContainingHtml = new StringValue('<h1>My description</h1><p>Lorem picsouuuuuuuuuuuuuuuuuuuu</p><ul><li>Item 1</li><li>Item 2</li><li><a href="https://akeneo.com">Link item</a></li></ul>');

        $this->applyOperation($cleanHTMLTagsOperation, $simpleStringValueContainingHtml)->shouldBeLike(new StringValue('test -> "'));
        $this->applyOperation($cleanHTMLTagsOperation, $complexStringValueContainingHtml)->shouldBeLike(new StringValue('My descriptionLorem picsouuuuuuuuuuuuuuuuuuuuItem 1Item 2Link item'));
    }

    public function it_does_nothing_when_string_value_does_not_contain_html_tags()
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();
        $stringValue = new StringValue('test');

        $this->applyOperation($cleanHTMLTagsOperation, $stringValue)->shouldBeLike($stringValue);
    }

    public function it_throws_exception_when_operation_is_invalid()
    {
        $notSupportedDefaultValueOperation = new DefaultValueOperation('n/a');
        $stringValue = new StringValue('test');

        $this->shouldThrow(\InvalidArgumentException::class)->during('applyOperation', [$notSupportedDefaultValueOperation, $stringValue]);
    }

    public function it_throws_exception_when_value_is_invalid()
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();
        $notSupportedBooleanValue = new BooleanValue(true);

        $this->shouldThrow(\InvalidArgumentException::class)->during('applyOperation', [$cleanHTMLTagsOperation, $notSupportedBooleanValue]);
    }
}
