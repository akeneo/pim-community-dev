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

use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class DefaultValueOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_default_value_operation_and_null_value()
    {
        $operation = DefaultValueOperation::createFromNormalized(['value' => 'n/a']);
        $value = new NullValue();

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = DefaultValueOperation::createFromNormalized(['value' => 'n/a']);
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_default_value_operation()
    {
        $operation = DefaultValueOperation::createFromNormalized(['value' => 'n/a']);
        $value = new NullValue();

        $this->applyOperation($operation, $value)->shouldBeLike(new StringValue('n/a'));
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = ReplacementOperation::createFromNormalized(['mapping' => []]);
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
