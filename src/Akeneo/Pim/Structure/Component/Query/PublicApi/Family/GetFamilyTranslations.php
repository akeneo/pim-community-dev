<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

interface GetFamilyTranslations
{
    public function byFamilyCodesAndLocale(array $familyCodes, string $locale): array;
}
