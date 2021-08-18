<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindGroupLabels;
use PhpSpec\ObjectBehavior;

class FindGroupLabelsSpec extends ObjectBehavior
{
    public function let(
        GetGroupTranslations $getGroupTranslations
    ): void {
        $this->beConstructedWith($getGroupTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindGroupLabels::class);
    }

    public function it_finds_the_labels_for_multiple_groups(
        GetGroupTranslations $getGroupTranslations
    ): void {
        $groupCodes = ['group1', 'group2', 'unknown'];
        $localeCode = 'fr_FR';

        $expectedResult = ['Groupe 1', 'Groupe 2'];
        $getGroupTranslations->byGroupCodesAndLocale($groupCodes, $localeCode)->willReturn($expectedResult);

        $this->byCodes($groupCodes, $localeCode)->shouldReturn($expectedResult);
    }
}
