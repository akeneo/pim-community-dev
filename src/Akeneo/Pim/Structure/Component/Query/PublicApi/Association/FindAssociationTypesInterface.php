<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

interface FindAssociationTypesInterface
{
    /**
     * @return AssociationType[]
     */
    public function execute(string $localeCode, int $limit, int $offset = 0, ?string $search = null): array;
}
