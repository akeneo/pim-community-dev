<?php

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesRemover;

interface CleanValuesOfRemovedAttributesInterface
{
    public function validateRemovedAttributesCodes(array $attributesCodes): void;

    public function countProductsWithRemovedAttribute(array $attributesCodes): int;

    public function cleanProductsWithRemovedAttribute(array $attributesCodes, ?callable $progress = null): void;

    public function countProductModelsWithRemovedAttribute(array $attributesCodes): int;

    public function cleanProductModelsWithRemovedAttribute(array $attributesCodes, ?callable $progress = null): void;

    public function countProductsAndProductModelsWithInheritedRemovedAttribute(array $attributesCodes): int;
}
