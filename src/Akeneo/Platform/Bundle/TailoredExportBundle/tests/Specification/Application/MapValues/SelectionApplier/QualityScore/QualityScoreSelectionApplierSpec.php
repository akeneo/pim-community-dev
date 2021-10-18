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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\QualityScore;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\Common\Selection\QualityScore\QualityScoreSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QualityScoreValue;
use PhpSpec\ObjectBehavior;

class QualityScoreSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new QualityScoreSelection('ecommerce', 'fr_FR');
        $value = new QualityScoreValue([
            'ecommerce' => [
                'fr_FR' => 'B'
            ]
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('B');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply QualityScore selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_quality_score_selection_with_quality_score_value()
    {
        $selection = new QualityScoreSelection('ecommerce', 'en_US');
        $value = new QualityScoreValue([
            'ecommerce' => [
                'fr_FR' => 'B'
            ]
        ]);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
