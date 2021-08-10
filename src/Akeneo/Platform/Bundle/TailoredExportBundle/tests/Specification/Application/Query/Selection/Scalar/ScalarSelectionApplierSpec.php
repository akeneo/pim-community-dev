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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Scalar;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Scalar\ScalarSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class ScalarSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new ScalarSelection();
        $value = new StringValue('some_data');

        $this->applySelection($selection, $value)->shouldReturn('some_data');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/', 'atmosphere', 'foo_attribute_code');
        $notSupportedValue = new AssetCollectionValue([], 'an_id', null, null);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Scalar selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_string_value_and_any_selection()
    {
        $selection = new ScalarSelection();
        $enabledSelection = new EnabledSelection();
        $value = new StringValue('some_data');

        $this->supports($selection, $value)->shouldReturn(true);
        $this->supports($enabledSelection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new AssetCollectionCodeSelection('/', 'atmosphere', 'foo_attribute_code');
        $notSupportedValue = new AssetCollectionValue([], 'an_id', null, null);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
