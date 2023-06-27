<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface GetMainIdentifierAttributeCode
{
    public function __invoke(): string;
}
