<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

interface FindFlattenAttributesInterface
{
    /**
     * Returns the attributes for the given attribute types.
     * The locale code is used to find the labels.
     *
     * @return FlattenAttribute[]
     */
    public function execute(
        string $localeCode,
        int $limit,
        ?array $attributeTypes = null,
        int $offset = 0,
        ?string $search = null
    ): array;
}
