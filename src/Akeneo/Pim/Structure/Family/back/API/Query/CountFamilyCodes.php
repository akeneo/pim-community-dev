<?php

namespace Akeneo\Pim\Structure\Family\API\Query;

interface CountFamilyCodes
{
    public function fromQuery(FamilyQuery $query): int;
}
