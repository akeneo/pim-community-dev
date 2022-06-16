<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\Uuid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresByUuidsQuery
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byProductUuid(UuidInterface $uuid): Read\Scores
    {
        $productScores = $this->byProductUuids([$uuid]);

        return $productScores[$uuid->toString()] ?? new Read\Scores(
            new ChannelLocaleRateCollection(),
            new ChannelLocaleRateCollection()
        );
    }

    public function byProductUuids(array $productUuids): array
    {
        if (empty($productUuids)) {
            return [];
        }

        $query = <<<SQL
SELECT BIN_TO_UUID(product.uuid) as uuid, latest_score.scores, latest_score.scores_partial_criteria
FROM pim_catalog_product product
INNER JOIN pim_data_quality_insights_product_score AS latest_score ON latest_score.product_uuid = product.uuid
LEFT JOIN pim_data_quality_insights_product_score AS younger_score
    ON younger_score.product_uuid = latest_score.product_uuid
    AND younger_score.evaluated_at > latest_score.evaluated_at
WHERE product.uuid IN (:product_uuids) 
  AND younger_score.evaluated_at IS NULL;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productsScores[$row['uuid']] = new Read\Scores(
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
