<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationRatesByProductsAndCriterionQuery implements GetEvaluationRatesByProductsAndCriterionQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
    }

    public function execute(ProductEntityIdCollection $productUuidCollection, CriterionCode $criterionCode): array
    {
        Assert::isInstanceOf($productUuidCollection, ProductUuidCollection::class);

        $ratesPath = sprintf('$."%s"', TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates']);

        $query = <<<SQL
SELECT BIN_TO_UUID(p.uuid) AS product_uuid, JSON_EXTRACT(e.result, '$ratesPath') AS rates
FROM pim_catalog_product p
    JOIN pim_data_quality_insights_product_criteria_evaluation e ON e.product_uuid = p.uuid
WHERE p.uuid IN (:productUuids) AND e.criterion_code = :criterionCode;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'productUuids' => $productUuidCollection->toArrayBytes(),
                'criterionCode' => $criterionCode,
            ],
            [
                'productUuids' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $evaluationRates = [];
        while ($evaluationResult = $stmt->fetchAssociative()) {
            $evaluationRates[$evaluationResult['product_uuid']] = $this->formatEvaluationRates($criterionCode, $evaluationResult);
        }

        return $evaluationRates;
    }

    private function formatEvaluationRates(CriterionCode $criterionCode, array $evaluationResult): array
    {
        if (!isset($evaluationResult['rates'])) {
            return [];
        }

        $rates = json_decode($evaluationResult['rates'], true, 512, JSON_THROW_ON_ERROR);
        $rates = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, [TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => $rates]);

        return $rates['rates'] ?? [];
    }
}
