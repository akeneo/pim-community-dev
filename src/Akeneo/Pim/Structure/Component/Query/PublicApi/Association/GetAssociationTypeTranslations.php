<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

interface GetAssociationTypeTranslations
{
    public function byAssociationTypeCodeAndLocale(array $associationTypeCodes, string $locale): array;
}
