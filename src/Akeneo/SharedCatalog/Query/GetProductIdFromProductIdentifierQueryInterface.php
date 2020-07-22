<?php

namespace Akeneo\SharedCatalog\Query;

interface GetProductIdFromProductIdentifierQueryInterface
{
    public function execute(string $productIdentifier): ?string;
}
