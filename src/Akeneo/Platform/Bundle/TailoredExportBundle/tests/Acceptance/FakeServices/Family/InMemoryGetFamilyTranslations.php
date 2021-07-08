<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations as GetFamilyTranslationsInterface;

final class InMemoryGetFamilyTranslations implements GetFamilyTranslationsInterface
{
    private array $familyLabels = [];

    public function addFamilyLabel(string $familyCode, string $locale, string $label)
    {
        $this->familyLabels[$familyCode][$locale] = $label;
    }

    public function byFamilyCodesAndLocale(array $familyCodes, string $locale): array
    {
        return array_reduce($familyCodes, function ($carry, $familyCode) use ($locale) {
            $carry[$familyCode] = $this->familyLabels[$familyCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
