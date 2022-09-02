<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;

final class ProductModelWordIgnoredEvent
{
    public function __construct(
        private ProductEntityIdInterface $productId
    ) {
        if (!$productId instanceof ProductModelId) {
            throw new \InvalidArgumentException(sprintf('Invalid ProductModelId, got %s', get_class($productId)));
        }
    }

    public function getProductId(): ProductEntityIdInterface
    {
        return $this->productId;
    }
}
