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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class BooleanReplacementOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_replacement_operation_and_boolean_value()
    {
        $operation = new ReplacementOperation([
            'true' => 'vrai',
            'false' => 'faux',
        ]);

        $value = new BooleanValue(true);

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_boolean_replacement_operation()
    {
        $operation = new ReplacementOperation([
            'true' => 'vrai',
            'false' => 'faux',
        ]);

        $trueValue = new BooleanValue(true);
        $falseValue = new BooleanValue(false);

        $this->applyOperation($operation, $trueValue)->shouldBeLike(new StringValue('vrai'));
        $this->applyOperation($operation, $falseValue)->shouldBeLike(new StringValue('faux'));
    }

    public function it_does_nothing_when_value_is_not_mapped()
    {
        $operation = new ReplacementOperation([]);

        $trueValue = new BooleanValue(true);
        $falseValue = new BooleanValue(false);

        $this->applyOperation($operation, $trueValue)->shouldReturn($trueValue);
        $this->applyOperation($operation, $falseValue)->shouldReturn($falseValue);
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
