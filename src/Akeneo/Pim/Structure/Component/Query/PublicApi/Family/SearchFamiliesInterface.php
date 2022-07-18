<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

interface SearchFamiliesInterface
{
    public function search(SearchFamiliesParameters $searchFamiliesParameters): SearchFamiliesResult;
}
