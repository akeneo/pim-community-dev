<?php

namespace Akeneo\Pim\Structure\Component\Query\InternalApi;

interface GetBlacklistedAttributeJobExecutionIdInterface
{
    public function forAttributeCode(string $attributeCode): ?int;
}
