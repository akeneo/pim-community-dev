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
    // TODO: finish this spec
}
