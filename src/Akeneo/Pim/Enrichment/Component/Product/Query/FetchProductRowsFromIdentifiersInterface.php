<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

interface FetchProductRowsFromIdentifiersInterface
{
    /**
     * @param array<string> $identifiers
     * @param array<string> $attributeCodes
     *
     * @return Row[]
     */
    public function __invoke(array $identifiers, array $attributeCodes, string $channelCode, string $localeCode): array;
}
