<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface GetAttributeTranslations
{
    /**
     * @return array<string, string>
     */
    public function byAttributeCodesAndLocale(array $attributeCodes, string $locale): array;

    /**
     * @return array<string , array<string, string>>
     */
    public function byAttributeCodes(array $attributeCodes): array;
}
