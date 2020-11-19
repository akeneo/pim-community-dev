<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductScores
{
    private ProductId $productId;

    private \DateTimeImmutable $evaluatedAt;

    private ChannelLocaleRateCollection $scores;

    public function __construct(ProductId $productId, \DateTimeImmutable $evaluatedAt, ChannelLocaleRateCollection $scores)
    {
        $this->productId = $productId;
        $this->evaluatedAt = $evaluatedAt;
        $this->scores = $scores;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getEvaluatedAt(): \DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getScores(): ChannelLocaleRateCollection
    {
        return $this->scores;
    }
}
