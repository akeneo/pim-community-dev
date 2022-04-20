<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

class ProductIdFactory implements ProductEntityIdFactoryInterface
{
    public function create(string $id): ProductEntityIdInterface
    {
        return ProductUuid::fromString($id);
    }

    public function createCollection(array $ids): ProductUuidCollection
    {
        return ProductUuidCollection::fromStrings($ids);
    }
}
