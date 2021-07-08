<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;

final class InMemoryFindUnitLabel implements FindUnitLabelInterface
{
    private array $unitLabels = [];

    public function addUnitLabel(string $familyCode, string $unitCode, string $locale, string $label)
    {
        $this->unitLabels[$familyCode][$unitCode][$locale] = $label;
    }

    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode, string $locale): ?string
    {
        return $this->unitLabels[$familyCode][$unitCode][$locale] ?? null;
    }
}
