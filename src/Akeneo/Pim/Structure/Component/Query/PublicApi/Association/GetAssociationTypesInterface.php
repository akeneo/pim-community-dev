<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

interface GetAssociationTypesInterface
{
    /**
     * @return AssociationType[]
     */
    public function forCodes(array $associationTypeCodes): array;
}
