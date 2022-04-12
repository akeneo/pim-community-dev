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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use PhpSpec\ObjectBehavior;

class CleanHTMLTagsOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_clean_html_tags_operation(): void
    {
        $this->supports(new CleanHTMLTagsOperation(), '<h1>test</h1>')->shouldReturn(true);
    }

    public function it_applies_clean_html_tags_operation(): void
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();

        $simpleStringValueContainingHtml = '<h1>test -&gt;&nbsp;&quot;</h1>';
        $complexStringValueContainingHtml = '<h1>My description</h1><p>Lorem picsouuuuuuuuuuuuuuuuuuuu</p><ul><li>Item 1</li><li>Item 2</li><li><a href="https://akeneo.com">Link item</a></li></ul>';

        $this->applyOperation($cleanHTMLTagsOperation, $simpleStringValueContainingHtml)
            ->shouldBeLike('test -> "');
        $this->applyOperation($cleanHTMLTagsOperation, $complexStringValueContainingHtml)
            ->shouldBeLike('My descriptionLorem picsouuuuuuuuuuuuuuuuuuuuItem 1Item 2Link item');
    }

    public function it_does_nothing_when_value_does_not_contain_html_tags(): void
    {
        $cleanHTMLTagsOperation = new CleanHTMLTagsOperation();
        $value = 'test';

        $this->applyOperation($cleanHTMLTagsOperation, $value)->shouldBeLike($value);
    }
}
