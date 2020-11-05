<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

interface GetUniqueAttributeCodes
{
    /**
     * @return string[]
     */
    public function all(): array;
}
