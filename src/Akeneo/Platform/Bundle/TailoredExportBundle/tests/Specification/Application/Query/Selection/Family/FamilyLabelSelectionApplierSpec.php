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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Family;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Family\FamilyLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyLabelInterface;
use PhpSpec\ObjectBehavior;

class FamilyLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindFamilyLabelInterface $findFamilyLabel)
    {
        $this->beConstructedWith($findFamilyLabel);
    }

    public function it_applies_the_selection(FindFamilyLabelInterface $findFamilyLabel)
    {
        $selection = new FamilyLabelSelection('fr_FR');
        $value = new FamilyValue('a_family_code');

        $findFamilyLabel->byCode('a_family_code', 'fr_FR')->willReturn('A Family Label');

        $this->applySelection($selection, $value)
            ->shouldReturn('A Family Label');
    }

    public function it_applies_selection_and_fallback_when_no_translation_is_found(
        FindFamilyLabelInterface $findFamilyLabel
    ) {
        $selection = new FamilyLabelSelection('fr_FR');
        $value = new FamilyValue('a_family_code');

        $findFamilyLabel->byCode('a_family_code', 'fr_FR')->willReturn(null);

        $this->applySelection($selection, $value)
            ->shouldReturn('[a_family_code]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Family selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_family_label_selection_with_family_value()
    {
        $selection = new FamilyLabelSelection('fr_FR');
        $value = new FamilyValue('a_family_code');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
