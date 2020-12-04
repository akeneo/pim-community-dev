<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\InternalApi;

interface IsAttributeCodeBlacklistedInterface
{
    public function execute(string $attributeCode): bool;
}
