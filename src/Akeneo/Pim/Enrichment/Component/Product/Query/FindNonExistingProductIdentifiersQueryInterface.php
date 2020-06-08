<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface FindNonExistingProductIdentifiersQueryInterface
{
    public function execute(array $productIdentifiers): array;
}
