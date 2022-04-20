<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class ProductWordIgnoredEvent
{
    public function __construct(
        private ProductEntityIdInterface $productId
    ) {
        if (!$productId instanceof ProductId) {
            throw new \InvalidArgumentException(sprintf('Invalid ProductId, got %s', get_class($productId)));
        }
    }

    public function getProductId(): ProductEntityIdInterface
    {
        return $this->productId;
    }
}
