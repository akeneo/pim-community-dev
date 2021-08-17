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

    private GetGroupTranslations $getGroupTranslations;

    public function __construct(GetGroupTranslations $getGroupTranslations)
    {
        $this->getGroupTranslations = $getGroupTranslations;
    }

    /**
     * @inheritDoc
     */
    public function byCodes(array $groupCodes, string $locale): array
    {
        return $this->getGroupTranslations->byGroupCodesAndLocale($groupCodes, $locale);
    }
}
