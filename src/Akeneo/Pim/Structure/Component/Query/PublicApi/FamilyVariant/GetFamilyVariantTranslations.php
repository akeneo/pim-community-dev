<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant;

interface GetFamilyVariantTranslations
{
    public function byFamilyVariantCodesAndLocale(array $familyVariantCodes, string $locale): array;
}
