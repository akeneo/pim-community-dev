<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

interface FetchProductRowsFromIdentiersInterface
{
    /**
     * @param array  $identifiers
     * @param array  $attributeCodes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return Row[]
     */
    public function __invoke(array $identifiers, array $attributeCodes, string $channelCode, string $localeCode): array;
}
