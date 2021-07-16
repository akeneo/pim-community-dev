<?php

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Group;

use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;

class InMemoryFindGroupLabels implements FindGroupLabelsInterface
{
    private array $groupLabels;

    public function addGroupLabel(string $groupCode, string $locale, string $optionTranslation)
    {
        $this->groupLabels[$groupCode][$locale] = $optionTranslation;
    }

    public function byCodes(array $groupCodes, string $locale): array
    {
        return array_reduce($groupCodes, function ($carry, $groupCode) use ($locale) {
            $carry[$groupCode] = $this->groupLabels[$groupCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
