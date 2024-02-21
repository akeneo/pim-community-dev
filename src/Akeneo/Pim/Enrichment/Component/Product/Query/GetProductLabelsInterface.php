<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface GetProductLabelsInterface
{
    public function byIdentifiersAndLocaleAndScope(array $codes, string $locale, string $scope): array;

    public function byUuidsAndLocaleAndScope(array $uuids, string $locale, string $scope): array;
}
