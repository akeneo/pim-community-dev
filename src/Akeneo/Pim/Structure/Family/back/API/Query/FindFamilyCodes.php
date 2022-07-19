<?php

namespace Akeneo\Pim\Structure\Family\API\Query;

interface FindFamilyCodes
{
    /**
     * @return string[]
     */
    public function fromQuery(FamilyQuery $query): array;
}
