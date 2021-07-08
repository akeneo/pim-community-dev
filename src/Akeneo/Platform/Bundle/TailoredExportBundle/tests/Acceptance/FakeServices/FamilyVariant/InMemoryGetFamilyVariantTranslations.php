<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\FamilyVariant;

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations as GetFamilyVariantTranslationsInterface;

final class InMemoryGetFamilyVariantTranslations implements GetFamilyVariantTranslationsInterface
{
    private array $familyVariantLabels = [];

    public function addFamilyVariantLabel(string $familyVariantCode, string $locale, string $label)
    {
        $this->familyVariantLabels[$familyVariantCode][$locale] = $label;
    }

    public function byFamilyVariantCodesAndLocale(array $familyVariantCodes, string $locale): array
    {
        return array_reduce($familyVariantCodes, function ($carry, $familyVariantCode) use ($locale) {
            $carry[$familyVariantCode] = $this->familyVariantLabels[$familyVariantCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
