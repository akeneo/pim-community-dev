<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\FamilyVariant;

use Akeneo\Platform\Syndication\Domain\Query\FindFamilyVariantLabelInterface;

final class InMemoryFindFamilyVariantLabel implements FindFamilyVariantLabelInterface
{
    private array $familyVariantLabels = [];

    public function addFamilyVariantLabel(string $familyVariantCode, string $locale, string $label): void
    {
        $this->familyVariantLabels[$familyVariantCode][$locale] = $label;
    }

    public function byCode(string $familyVariantCode, string $locale): ?string
    {
        return $this->familyVariantLabels[$familyVariantCode][$locale] ?? null;
    }
}
