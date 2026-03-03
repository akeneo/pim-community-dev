<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface AttributeIsAFamilyVariantAxisInterface
{
    public function execute(string $attributeCode): bool;
}
