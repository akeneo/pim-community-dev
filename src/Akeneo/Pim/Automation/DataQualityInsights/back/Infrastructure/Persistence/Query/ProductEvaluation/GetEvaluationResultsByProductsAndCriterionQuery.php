<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationResultsByProductsAndCriterionQuery implements GetEvaluationResultsByProductsAndCriterionQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductEntityIdCollection $productIdCollection, CriterionCode $criterionCode): array
    {
        Assert::isInstanceOf($productIdCollection, ProductUuidCollection::class);

        $query = <<<SQL
SELECT BIN_TO_UUID(p.uuid) AS product_uuid, e.result
FROM pim_data_quality_insights_product_criteria_evaluation e
    JOIN pim_catalog_product p ON p.uuid = e.product_uuid
WHERE p.uuid IN (:productUuids) AND e.criterion_code = :criterionCode;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'productUuids' => $productIdCollection->toArrayBytes(),
                'criterionCode' => $criterionCode,
            ],
            [
                'productUuids' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $evaluationResults = [];
        while ($evaluation = $stmt->fetchAssociative()) {
            $evaluationResults[(string) $evaluation['product_uuid']] = $this->hydrateEvaluationResult($criterionCode, $evaluation['result']);
        }

        return $evaluationResults;
    }

    private function hydrateEvaluationResult(CriterionCode $criterionCode, ?string $rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, $rawResult);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
