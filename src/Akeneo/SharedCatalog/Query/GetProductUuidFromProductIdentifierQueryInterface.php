<?php

namespace Akeneo\SharedCatalog\Query;

use Ramsey\Uuid\UuidInterface;

interface GetProductUuidFromProductIdentifierQueryInterface
{
    public function execute(string $productIdentifier): ?UuidInterface;
}
