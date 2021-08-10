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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyVariantLabelInterface;
use PhpSpec\ObjectBehavior;

class FamilyVariantLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindFamilyVariantLabelInterface $findFamilyVariantLabel)
    {
        $this->beConstructedWith($findFamilyVariantLabel);
    }

    public function it_applies_the_selection(FindFamilyVariantLabelInterface $findFamilyVariantLabel)
    {
        $selection = new FamilyVariantLabelSelection('fr_FR');
        $value = new FamilyVariantValue('a_family_variant_code');

        $findFamilyVariantLabel->byCode('a_family_variant_code', 'fr_FR')->willReturn('A FamilyVariant Label');

        $this->applySelection($selection, $value)
            ->shouldReturn('A FamilyVariant Label');
    }

    public function it_applies_the_selection_and_fallback_when_no_translation_is_found(
        FindFamilyVariantLabelInterface $findFamilyVariantLabel
    ) {
        $selection = new FamilyVariantLabelSelection('fr_FR');
        $value = new FamilyVariantValue('a_family_variant_code');

        $findFamilyVariantLabel->byCode('a_family_variant_code', 'fr_FR')->willReturn(null);

        $this->applySelection($selection, $value)
            ->shouldReturn('[a_family_variant_code]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply FamilyVariant selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_family_variant_label_selection_with_family_variant_value()
    {
        $selection = new FamilyVariantLabelSelection('fr_FR');
        $value = new FamilyVariantValue('a_family_variant_code');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
