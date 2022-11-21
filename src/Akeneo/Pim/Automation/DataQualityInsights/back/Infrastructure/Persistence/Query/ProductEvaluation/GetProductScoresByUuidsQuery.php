<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresByUuidsQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresByUuidsQuery implements GetProductScoresByUuidsQueryInterface
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
SELECT BIN_TO_UUID(product.uuid) AS uuid, product_score.scores, product_score.scores_partial_criteria
FROM pim_catalog_product product
INNER JOIN pim_data_quality_insights_product_score AS product_score 
    ON product_score.product_uuid = product.uuid
WHERE product.uuid IN(:productUuids);
SQL;

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['productUuids' => $uuidsAsBytes],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
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
