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
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class BooleanSelectionHandlerSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $booleanSelection = new BooleanSelection();
        $booleanValue = new BooleanValue(true);

        $this->applySelection($booleanSelection, $booleanValue)
            ->shouldReturn('1');
    }

    public function it_does_not_applies_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/');
        $notSupportedValue = new AssetCollectionValue([]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Boolean selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_boolean_selection_with_boolean_value()
    {
        $booleanSelection = new BooleanSelection();
        $booleanValue = new BooleanValue(false);

        $this->supports($booleanSelection, $booleanValue)->shouldReturn(true);
    }

    public function it_does_not_supports_other_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/');
        $notSupportedValue = new AssetCollectionValue([]);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
