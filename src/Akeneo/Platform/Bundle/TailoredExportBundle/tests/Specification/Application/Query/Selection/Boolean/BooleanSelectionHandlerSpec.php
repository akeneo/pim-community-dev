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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class BooleanSelectionHandlerSpec extends ObjectBehavior
{
    public function it_applies_the_selection_when_value_is_true()
    {
        $selection = new BooleanSelection();
        $value = new BooleanValue(true);

        $this->applySelection($selection, $value)
            ->shouldReturn('1');
    }

    public function it_applies_the_selection_when_value_is_false()
    {
        $selection = new BooleanSelection();
        $value = new BooleanValue(false);

        $this->applySelection($selection, $value)
            ->shouldReturn('0');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/', 'packshot', 'foo_attribute_code');
        $notSupportedValue = new AssetCollectionValue([], 'an_id', null, null);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Boolean selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_boolean_selection_with_boolean_value()
    {
        $selection = new BooleanSelection();
        $value = new BooleanValue(false);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/', 'packshot', 'foo_attribute_code');
        $notSupportedValue = new AssetCollectionValue([], 'the_id', null, null);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
