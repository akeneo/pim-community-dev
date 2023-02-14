<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\InternalApi;

interface CountAttributes
{
    public function byCodes(?array $includeCodes = null, ?array $excludeCodes = null): int;
}
