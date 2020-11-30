<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface CountProductModelsWithRemovedAttributeInterface
{
    public function count(array $attributesCodes): int;
}
