<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

interface AttributeValueExtractorInterface
{
    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string;

    public function support(string $attributeType): bool;
}
