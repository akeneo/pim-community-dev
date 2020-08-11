<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Category;

interface GetCategoryTranslations
{
    public function byCategoryCodesAndLocale(array $categoryCodes, string $locale): array;
}
