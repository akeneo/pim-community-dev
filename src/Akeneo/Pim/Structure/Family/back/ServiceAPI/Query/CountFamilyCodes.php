<?php

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

interface CountFamilyCodes
{
    public function fromQuery(CountFamilyQuery $query): int;
}
