<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

interface GetFamilies
{
    public function byCode(string $familyCode): ?Family;

    /** @return array<string, Family> */
    public function byCodes(array $familyCodes): array;
}
