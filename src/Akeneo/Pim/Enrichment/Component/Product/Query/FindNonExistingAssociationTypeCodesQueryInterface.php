<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

interface FindNonExistingAssociationTypeCodesQueryInterface
{
    public function execute(array $codes): array;
}
