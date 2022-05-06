<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresByIdentifiersQuery implements GetProductScoresByIdentifiersQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byProductIdentifier(string $identifier): ChannelLocaleRateCollection
    {
        $productScores = $this->byProductIdentifiers([$identifier]);

        return $productScores[$identifier] ?? new ChannelLocaleRateCollection();
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
SELECT product.identifier, product_score.scores
FROM pim_catalog_product product
INNER JOIN pim_data_quality_insights_product_score AS product_score 
    ON product_score.product_id = product.id
WHERE product.identifier IN(:product_identifiers);
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productsScores[$row['identifier']] = $this->hydrateScores($row['scores']);
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
