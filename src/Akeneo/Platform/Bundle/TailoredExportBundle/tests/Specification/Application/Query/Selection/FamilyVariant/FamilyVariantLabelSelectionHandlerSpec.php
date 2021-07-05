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

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyVariantValue;
use PhpSpec\ObjectBehavior;

class FamilyVariantLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(GetFamilyVariantTranslations $getFamilyVariantTranslations)
    {
        $this->beConstructedWith($getFamilyVariantTranslations);
    }

    public function it_applies_the_selection(GetFamilyVariantTranslations $getFamilyVariantTranslations)
    {
        $selection = new FamilyVariantLabelSelection('fr_FR');
        $value = new FamilyVariantValue('a_family_variant_code');

        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale(
            ['a_family_variant_code'],
            'fr_FR'
        )->willReturn([
            'a_family_variant_code' => 'A FamilyVariant Label',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('A FamilyVariant Label');
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
