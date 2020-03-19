<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductModelTitleSuggestionIgnoredEvent extends Event
{
    public const TITLE_SUGGESTION_IGNORED = "data_quality_product_model_title_suggestion_ignored";

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
