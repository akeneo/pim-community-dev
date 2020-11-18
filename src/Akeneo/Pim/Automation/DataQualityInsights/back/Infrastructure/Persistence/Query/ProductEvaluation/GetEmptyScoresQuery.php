<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Accommodation to handle empty scores for product models
 */
final class GetEmptyScoresQuery implements GetLatestProductScoresQueryInterface
{
    public function byProductId(ProductId $productId): ChannelLocaleRateCollection
    {
        return new ChannelLocaleRateCollection();
    }

    public function byProductIds(array $productIds): array
    {
        $productsScores = [];
        foreach ($productIds as $productId) {
            $productsScores[strval($productId)] = new ChannelLocaleRateCollection();
        }

        return $productsScores;
    }
}
