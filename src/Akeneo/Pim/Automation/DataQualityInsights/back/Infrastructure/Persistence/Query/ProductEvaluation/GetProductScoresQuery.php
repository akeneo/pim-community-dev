<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresQuery implements GetProductScoresQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public function byProductId(ProductEntityIdInterface $productId): ChannelLocaleRateCollection
    {
        $productIdCollection = $this->idFactory->createCollection([(string)$productId]);
        $productScores = $this->byProductIds($productIdCollection);

        return $productScores[(string)$productId] ?? new ChannelLocaleRateCollection();
    }

    public function byProductIds(ProductEntityIdCollection $productIdCollection): array
    {
        if ($productIdCollection->isEmpty()) {
            return [];
        }

        $query = <<<SQL
SELECT product_id, scores
FROM pim_data_quality_insights_product_score
WHERE product_id IN(:product_ids)
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIdCollection->toArrayString()],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productId = $row['product_id'];
            $productsScores[$productId] = $this->hydrateScores($row['scores']);
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
