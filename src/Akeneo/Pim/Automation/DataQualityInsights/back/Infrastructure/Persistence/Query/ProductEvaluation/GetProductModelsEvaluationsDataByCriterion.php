<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductsEvaluationsDataByCriterionInterface;
use Doctrine\DBAL\Connection;

class GetProductModelsEvaluationsDataByCriterion implements GetProductsEvaluationsDataByCriterionInterface
{
    public function __construct(
        private Connection $dbConnection
    ) {
    }

    public function execute(string $criterionCode, array $productIds): array
    {
        $query = <<<SQL
SELECT product_id, result
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN(:productIds) AND criterion_code = :criterionCode
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            [
                'productIds' => $productIds,
                'criterionCode' => $criterionCode,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
            ]
        )->fetchAllAssociative();
    }
}
