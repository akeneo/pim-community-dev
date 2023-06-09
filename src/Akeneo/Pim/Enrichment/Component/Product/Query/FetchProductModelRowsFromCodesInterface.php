<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

interface FetchProductModelRowsFromCodesInterface
{
    /**
     * @param array  $codes
     * @param array  $attributeCodes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return Row[]
     */
    public function __invoke(array $codes, array $attributeCodes, string $channelCode, string $localeCode): array;
}
