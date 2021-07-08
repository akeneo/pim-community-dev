<?php

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface GetSystemSources
{
    public function execute(string $localeCode, int $limit, int $offset = 0, string $search = null): array;
}
