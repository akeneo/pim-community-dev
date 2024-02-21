<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductModelScoreRepositoryInterface;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelScoreRepository implements ProductModelScoreRepositoryInterface
{
    public function __construct(
        private Connection $dbConnection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $productsScores): void
    {
        if (empty($productsScores)) {
            return;
        }

        Assert::allIsInstanceOf($productsScores, Write\ProductScores::class);

        $queries = '';
        $queriesParameters = [];
        $queriesParametersTypes = [];

        foreach ($productsScores as $index => $productModelScore) {
            $productModelId = sprintf('productModelId_%d', $index);
            $evaluatedAt = sprintf('evaluatedAt_%d', $index);
            $scores = sprintf('scores_%d', $index);
            $scoresPartialCriteria = sprintf('scores_partial_criteria_%d', $index);

            $queries .= <<<SQL
INSERT IGNORE INTO pim_data_quality_insights_product_model_score (product_model_id, evaluated_at, scores, scores_partial_criteria)
VALUES (:$productModelId, :$evaluatedAt, :$scores, :$scoresPartialCriteria)
ON DUPLICATE KEY UPDATE evaluated_at = :$evaluatedAt, scores = :$scores, scores_partial_criteria = :$scoresPartialCriteria;
SQL;
            $queriesParameters[$productModelId] = (string)$productModelScore->getEntityId();
            $queriesParametersTypes[$productModelId] = \PDO::PARAM_INT;
            $queriesParameters[$evaluatedAt] = $productModelScore->getEvaluatedAt()->format('Y-m-d');
            $queriesParameters[$scores] = \json_encode($productModelScore->getScores()->toNormalizedRates());
            $queriesParameters[$scoresPartialCriteria] = \json_encode($productModelScore->getScoresPartialCriteria()->toNormalizedRates());
        }

        $this->dbConnection->executeQuery($queries, $queriesParameters, $queriesParametersTypes);
    }
}
