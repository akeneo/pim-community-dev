<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface GetProductLabelsInterface
{
    public function byCodesAndLocaleAndScope(array $codes, string $locale, string $scope): array;
}
