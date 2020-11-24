<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface CountProductsWithRemovedAttributeInterface
{
    public function count(array $attributesCodes): int;
}
