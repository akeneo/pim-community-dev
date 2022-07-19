<?php

namespace Akeneo\Pim\Structure\Family\API\Query;

use Akeneo\Pim\Structure\Family\API\Model\FamilyWithLabelsCollection;

interface FindFamiliesWithLabels
{
    public function fromQuery(FamilyQuery $query): FamilyWithLabelsCollection;
}
