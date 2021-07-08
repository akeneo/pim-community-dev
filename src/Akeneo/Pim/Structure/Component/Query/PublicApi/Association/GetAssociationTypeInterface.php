<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

interface GetAssociationTypeInterface
{
    public function execute(string $associationTypeCode): ?AssociationType;
}
