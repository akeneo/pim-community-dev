<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface GetProductModelLabelsInterface
{
    public function byCodesAndLocaleAndScope(array $codes, string $locale, string $scope): array;
}
