<?php

namespace Akeneo\Platform\TailoredImport\Domain\Query\Family;

interface FindFamiliesInterface
{
    public function execute(
        string $localeCode,
        int $limit,
        int $page = 0,
        string $search = null,
        ?array $includeCodes = null,
        ?array $excludeCodes = null,
    ): FindFamiliesResult;
}
