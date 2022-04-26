<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

// TODO Rename to ProductUuidFactory
class ProductIdFactory implements ProductEntityIdFactoryInterface
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
