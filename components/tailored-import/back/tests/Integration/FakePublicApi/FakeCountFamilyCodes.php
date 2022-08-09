<?php

namespace Akeneo\Platform\TailoredImport\Test\Integration\FakePublicApi;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;

class FakeCountFamilyCodes implements CountFamilyCodes
{
    public function __construct(
        private FindFamiliesWithLabels $findFamiliesWithLabels
    ) {
    }

    public function fromQuery(FamilyQuery $query): int
    {
        $families = $this->findFamiliesWithLabels->fromQuery($query);

        return count($families);
    }
}
