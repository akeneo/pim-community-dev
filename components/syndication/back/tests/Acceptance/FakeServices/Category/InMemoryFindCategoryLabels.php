<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Category;

use Akeneo\Platform\Syndication\Domain\Query\FindCategoryLabelsInterface;

final class InMemoryFindCategoryLabels implements FindCategoryLabelsInterface
{
    private array $categoryLabels = [];

    public function addCategoryLabel(string $categoryCode, string $locale, string $label): void
    {
        $this->categoryLabels[$categoryCode][$locale] = $label;
    }

    public function byCodes(array $categoryCodes, string $locale): array
    {
        return array_reduce($categoryCodes, function ($carry, $categoryCode) use ($locale) {
            $carry[$categoryCode] = $this->categoryLabels[$categoryCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
