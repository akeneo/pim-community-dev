<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

interface FetchProductRowsFromUuidsInterface
{
    /**
     * @param array<string> $uuids
     * @param array<string> $attributeCodes
     *
     * @return Row[]
     */
    public function __invoke(array $uuids, array $attributeCodes, string $channelCode, string $localeCode): array;
}
