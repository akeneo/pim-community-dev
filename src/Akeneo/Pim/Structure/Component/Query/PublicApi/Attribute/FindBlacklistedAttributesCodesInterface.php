<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface FindBlacklistedAttributesCodesInterface
{
    /** @return string[] */
    public function all(): array;
}
