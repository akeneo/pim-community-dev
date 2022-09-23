<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Family;

use Akeneo\Platform\Syndication\Domain\Query\FindFamilyLabelInterface;

final class InMemoryFindFamilyLabel implements FindFamilyLabelInterface
{
    private array $familyLabels = [];

    public function addFamilyLabel(string $familyCode, string $locale, string $label): void
    {
        $this->familyLabels[$familyCode][$locale] = $label;
    }

    public function byCode(string $familyCode, string $locale): ?string
    {
        return $this->familyLabels[$familyCode][$locale] ?? null;
    }
}
