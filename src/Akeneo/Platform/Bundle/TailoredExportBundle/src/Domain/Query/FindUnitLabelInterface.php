<?php

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindUnitLabelInterface
{
    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode, string $locale): ?string;
}
