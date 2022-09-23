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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\OperationApplier\String;

use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\ExtractOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class ExtractOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_extract_operation_and_null_value()
    {
        $operation = new ExtractOperation('/[a-z]*/');
        $value = new StringValue('nice_value');

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new ExtractOperation('/[a-z]*/');
        $notSupportedValue = new NullValue();

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_extract_operation()
    {
        $operation = new ExtractOperation('/[a-z]*/');
        $value = new StringValue('nice_value');

        $this->applyOperation($operation, $value)->shouldBeLike(new StringValue('nice'));
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new ReplacementOperation([]);
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
