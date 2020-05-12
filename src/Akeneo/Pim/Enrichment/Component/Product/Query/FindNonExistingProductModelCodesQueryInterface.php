<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface FindNonExistingProductModelCodesQueryInterface
{
    public function execute(array $productModelCodes): array;
}
