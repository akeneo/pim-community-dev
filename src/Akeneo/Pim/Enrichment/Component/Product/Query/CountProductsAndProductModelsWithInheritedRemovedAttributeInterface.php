<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface CountProductsAndProductModelsWithInheritedRemovedAttributeInterface
{
    public function count(array $attributesCodes): int;
}
