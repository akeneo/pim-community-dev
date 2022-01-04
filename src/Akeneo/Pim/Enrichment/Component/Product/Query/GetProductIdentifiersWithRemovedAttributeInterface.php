<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

interface GetProductIdentifiersWithRemovedAttributeInterface
{
    public function nextBatch(array $attributesCodes, int $batchSize): iterable;

    public function getQueryBuilder(): SearchQueryBuilder;
}
