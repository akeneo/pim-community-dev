<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

class ProductUuidFactory implements ProductEntityIdFactoryInterface
{
    public function create(string $uuid): ProductUuid
    {
        return ProductUuid::fromString($uuid);
    }

    public function createCollection(array $uuids): ProductUuidCollection
    {
        return ProductUuidCollection::fromStrings($uuids);
    }
}
