<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;

class ProductModelIdFactory implements ProductEntityIdFactoryInterface
{
    public function create(string $id): ProductModelId
    {
        return ProductModelId::fromString($id);
    }

    public function createCollection(array $ids): ProductModelIdCollection
    {
        return ProductModelIdCollection::fromStrings($ids);
    }
}
