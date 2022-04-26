<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface GetAttributeTranslations
{
    /**
     * @return array<string: attributeCode, string: label>
     */
    public function byAttributeCodesAndLocale(array $attributeCodes, string $locale): array;

    /**
     * @return array<string: attributeCode, array<string: locale, string: label>>
     */
    public function byAttributeCodes(array $attributeCodes): array;
}
