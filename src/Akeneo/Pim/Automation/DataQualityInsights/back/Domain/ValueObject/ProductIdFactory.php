<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

class ProductIdFactory implements ProductEntityIdFactoryInterface
{
    public function create(string $id): ProductEntityIdInterface
    {
        return ProductId::fromString($id);
    }

    public function createCollection(array $ids): ProductIdCollection
    {
        return ProductIdCollection::fromStrings($ids);
    }
}
