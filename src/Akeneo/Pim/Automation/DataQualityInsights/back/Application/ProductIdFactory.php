<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;

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
