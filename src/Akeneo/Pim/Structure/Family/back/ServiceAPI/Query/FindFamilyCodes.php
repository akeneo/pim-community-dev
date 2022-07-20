<?php

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

interface FindFamilyCodes
{
    /**
     * @return string[]
     */
    public function fromQuery(FindFamilyQuery $query): array;
}
