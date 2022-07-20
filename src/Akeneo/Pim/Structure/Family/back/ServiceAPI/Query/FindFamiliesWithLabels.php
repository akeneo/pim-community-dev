<?php

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

use Akeneo\Pim\Structure\Family\ServiceAPI\Model\FamilyWithLabelsCollection;

interface FindFamiliesWithLabels
{
    public function fromQuery(FindFamilyQuery $query): FamilyWithLabelsCollection;
}
