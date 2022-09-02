<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresByCodesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresByCodesQuery implements GetProductModelScoresByCodesQueryInterface
{
    public function __construct(
        private Connection $dbConnection
    ) {
    }

    public function byProductModelCode(string $productModelCode): Read\Scores
    {
        $productModelScores = $this->byProductModelCodes([$productModelCode]);

        return $productModelScores[$productModelCode] ?? new Read\Scores(
            new ChannelLocaleRateCollection(),
            new ChannelLocaleRateCollection()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function byProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $query = <<<SQL
SELECT pcpm.code, product_model_score.scores, product_model_score.scores_partial_criteria
FROM pim_data_quality_insights_product_model_score AS product_model_score
LEFT JOIN pim_catalog_product_model pcpm ON pcpm.id = product_model_score.product_model_id
WHERE pcpm.code IN (:productModelCodes);
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $productModelsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productModelsScores[$row['code']] = new Read\Scores(
                $this->hydrateScores($row['scores']),
                $this->hydrateScores($row['scores_partial_criteria'] ?? '{}'),
            );
        }

        return $productModelsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
