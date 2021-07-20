<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Category;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations as GetCategoryTranslationsInterface;

final class InMemoryGetCategoryTranslations implements GetCategoryTranslationsInterface
{
    private array $categoryLabels = [];

    public function addCategoryLabel(string $categoryCode, string $locale, string $label)
    {
        $this->categoryLabels[$categoryCode][$locale] = $label;
    }

    public function byCategoryCodesAndLocale(array $categoryCodes, string $locale): array
    {
        return array_reduce($categoryCodes, function ($carry, $categoryCode) use ($locale) {
            $carry[$categoryCode] = $this->categoryLabels[$categoryCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
