<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

class ProductModelIdFactory implements ProductEntityIdFactoryInterface
{

    public function create(string $id): ProductEntityIdInterface
    {
        return ProductModelId::fromString($id);
    }

    public function createCollection(array $ids): ProductIdCollection
    {
        return ProductIdCollection::fromStrings($ids);
    }
}
