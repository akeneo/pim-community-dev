<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Group;

interface GetGroupTranslations
{
    public function byGroupCodesAndLocale(array $groupCodes, string $locale): array;
}
