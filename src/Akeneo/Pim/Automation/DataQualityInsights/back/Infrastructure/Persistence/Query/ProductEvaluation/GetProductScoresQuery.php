<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

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

    public function byProductUuid(ProductEntityIdInterface $productUuid): Read\Scores
    {
        $productIdCollection = $this->idFactory->createCollection([(string)$productUuid]);
        $productScores = $this->byProductUuidCollection($productIdCollection);

        return $productScores[(string)$productUuid] ?? new Read\Scores(
            new ChannelLocaleRateCollection(),
            new ChannelLocaleRateCollection()
        );
    }

    public function byProductUuidCollection(ProductEntityIdCollection $productUuidCollection): array
    {
        if ($productUuidCollection->isEmpty()) {
            return [];
        }

        Assert::isInstanceOf($productUuidCollection, ProductUuidCollection::class);

        $query = <<<SQL
SELECT BIN_TO_UUID(product_uuid) AS product_uuid, scores, scores_partial_criteria
FROM pim_data_quality_insights_product_score
WHERE product_uuid IN(:product_uuids)
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => $productUuidCollection->toArrayBytes()],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productUuid = $row['product_uuid'];
            $productsScores[$productUuid] = new Read\Scores(
                $this->hydrateScores($row['scores']),
                $this->hydrateScores($row['scores_partial_criteria'] ?? '{}'),
            );
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
