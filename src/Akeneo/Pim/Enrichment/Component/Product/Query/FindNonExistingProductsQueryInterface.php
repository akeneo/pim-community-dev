<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface FindNonExistingProductsQueryInterface
{
    /**
     * @param string[] $productIdentifiers
     * @return string[]
     */
    public function byProductIdentifiers(array $productIdentifiers): array;

    /**
     * @param string[] $productUuids
     * @return string[]
     */
    public function byProductUuids(array $productUuids): array;
}
