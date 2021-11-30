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
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class EnabledReplacementOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_replacement_operation_and_enabled_value()
    {
        $operation = new ReplacementOperation([
            'true' => 'activé',
            'false' => 'désactivé',
        ]);

        $value = new EnabledValue(true);

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_enabled_replacement_operation()
    {
        $operation = new ReplacementOperation([
            'true' => 'activé',
            'false' => 'désactivé',
        ]);

        $enabledValue = new EnabledValue(true);
        $disabledValue = new EnabledValue(false);

        $this->applyOperation($operation, $enabledValue)->shouldBeLike(new StringValue('activé'));
        $this->applyOperation($operation, $disabledValue)->shouldBeLike(new StringValue('désactivé'));
    }

    public function it_does_nothing_when_value_is_not_mapped()
    {
        $operation = new ReplacementOperation([]);

        $enabledValue = new EnabledValue(true);
        $disabledValue = new EnabledValue(false);

        $this->applyOperation($operation, $enabledValue)->shouldReturn($enabledValue);
        $this->applyOperation($operation, $disabledValue)->shouldReturn($disabledValue);
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
