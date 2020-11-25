<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface GetProductIdentifiersWithRemovedAttributeInterface
{
    public function nextBatch(array $attributesCodes, int $batchSize): iterable;
}
