<?php

namespace Akeneo\Catalogs\Infrastructure\Service\Mapping;

class AbstractAttributeService
{
    protected function getProductAttributeValue(array $product, ?string $attributeCode, ?string $locale, ?string $scope): string | null
    {
        $scope ??= '<all_channels>';
        $locale ??= '<all_locales>';

        return $product['raw_values'][$attributeCode][$scope][$locale] ?? null;
    }
}
