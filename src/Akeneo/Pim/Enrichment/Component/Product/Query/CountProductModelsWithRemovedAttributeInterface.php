<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

interface CountProductModelsWithRemovedAttributeInterface
{
    public function count(array $attributesCodes, bool $includeProductModelsWithoutValue = true): int;

    public function getQueryBuilder(): SearchQueryBuilder;
}
