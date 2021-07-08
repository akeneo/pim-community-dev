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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Groups;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Groups\GroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use PhpSpec\ObjectBehavior;

class GroupsLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(GetGroupTranslations $getGroupTranslations)
    {
        $this->beConstructedWith($getGroupTranslations);
    }

    public function it_applies_the_selection(GetGroupTranslations $getGroupTranslations)
    {
        $selection = new GroupsLabelSelection('/', 'fr_FR');
        $value = new GroupsValue(['group1', 'group2']);

        $getGroupTranslations->byGroupCodesAndLocale(
            ['group1', 'group2'],
            'fr_FR'
        )->willReturn([
            'group1' => 'Group 1',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Group 1/[group2]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Groups selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_groups_label_selection_with_groups_value()
    {
        $selection = new GroupsLabelSelection('/', 'en_US');
        $value = new GroupsValue(['group1', 'group2']);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
