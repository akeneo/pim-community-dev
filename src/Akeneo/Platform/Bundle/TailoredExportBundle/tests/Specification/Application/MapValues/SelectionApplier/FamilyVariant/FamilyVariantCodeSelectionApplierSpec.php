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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\FamilyVariant;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\FamilyVariant\FamilyVariantCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyVariantValue;
use PhpSpec\ObjectBehavior;

class FamilyVariantCodeSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new FamilyVariantCodeSelection();
        $value = new FamilyVariantValue('a_family_variant_code');

        $this->applySelection($selection, $value)
            ->shouldReturn('a_family_variant_code');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply FamilyVariant selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_family_variant_code_selection_with_family_variant_value()
    {
        $selection = new FamilyVariantCodeSelection();
        $value = new FamilyVariantValue('');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
