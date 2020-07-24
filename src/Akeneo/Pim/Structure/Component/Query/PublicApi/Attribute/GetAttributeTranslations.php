<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface GetAttributeTranslations
{
    public function byAttributeCodesAndLocale(array $attributeCodes, string $locale);
}
