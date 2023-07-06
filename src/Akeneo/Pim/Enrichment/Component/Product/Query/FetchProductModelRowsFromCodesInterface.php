<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

interface FetchProductModelRowsFromCodesInterface
{
    /**
     * @param array<string> $codes
     * @param array<string> $attributeCodes
     *
     * @return Row[]
     */
    public function __invoke(array $codes, array $attributeCodes, string $channelCode, string $localeCode): array;
}
