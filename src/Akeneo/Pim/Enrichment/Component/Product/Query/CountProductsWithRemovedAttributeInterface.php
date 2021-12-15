<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

interface CountProductsWithRemovedAttributeInterface
{
    public function count(array $attributesCodes): int;

    public function getQueryBuilder(): SearchQueryBuilder;
}
