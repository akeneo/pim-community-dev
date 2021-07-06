<?php

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Group;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;

class InMemoryGetGroupTranslations implements GetGroupTranslations
{
    private array $groupLabels;

    public function addGroupTranslation(string $groupCode, string $locale, string $optionTranslation)
    {
        $this->groupLabels[$groupCode][$locale] = $optionTranslation;
    }

    public function byGroupCodesAndLocale(array $groupCodes, string $locale): array
    {
        return array_reduce($groupCodes, function ($carry, $groupCode) use ($locale) {
            $carry[$groupCode] = $this->groupLabels[$groupCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
