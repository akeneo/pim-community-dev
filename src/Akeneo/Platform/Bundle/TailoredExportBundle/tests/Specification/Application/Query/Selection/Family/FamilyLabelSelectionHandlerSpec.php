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

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Family\FamilyLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyValue;
use PhpSpec\ObjectBehavior;

class FamilyLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->beConstructedWith($getFamilyTranslations);
    }

    public function it_applies_the_selection(GetFamilyTranslations $getFamilyTranslations)
    {
        $selection = new FamilyLabelSelection('fr_FR');
        $value = new FamilyValue('a_family_code');

        $getFamilyTranslations->byFamilyCodesAndLocale(
            ['a_family_code'],
            'fr_FR'
        )->willReturn([
            'a_family_code' => 'A Family Label',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('A Family Label');
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
