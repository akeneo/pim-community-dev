<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface GetProductModelIdentifiersWithRemovedAttributeInterface
{
    public function nextBatch(array $attributesCodes, int $batchSize): iterable;
}
