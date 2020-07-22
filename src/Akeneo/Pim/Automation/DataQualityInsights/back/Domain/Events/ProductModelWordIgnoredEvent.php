<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class ProductModelWordIgnoredEvent
{
    /** @var ProductId */
    private $productId;

    public function __construct(ProductId $productId)
    {
        $this->productId = $productId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }
}
