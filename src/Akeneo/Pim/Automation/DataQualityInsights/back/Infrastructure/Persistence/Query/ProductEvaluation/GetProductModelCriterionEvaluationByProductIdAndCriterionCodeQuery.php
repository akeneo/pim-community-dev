<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Hydrator\hydrateCriterionEvaluationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelCriterionEvaluationByProductIdAndCriterionCodeQuery implements GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private hydrateCriterionEvaluationResult $hydrateCriterionEvaluationResult
    ) {
    }

    public function execute(ProductEntityIdInterface $productId, CriterionCode $criterionCode): ?Read\CriterionEvaluation
    {
        $query = <<<SQL
SELECT evaluated_at, status, result 
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id = :productId AND criterion_code = :criterionCode;
SQL;
        $rawEvaluation = $this->dbConnection->executeQuery(
            $query,
            ['productId' => (string)$productId, 'criterionCode' => $criterionCode],
            ['productId' => Types::INTEGER, 'criterionCode' => Types::STRING],
        )->fetchAssociative();

        if (!$rawEvaluation) {
            return null;
        }

        return new Read\CriterionEvaluation(
            $criterionCode,
            $productId,
            null !== $rawEvaluation['evaluated_at'] ? Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($rawEvaluation['evaluated_at'], $this->dbConnection->getDatabasePlatform()) : null,
            new CriterionEvaluationStatus($rawEvaluation['status']),
            null !== $rawEvaluation['result'] ? ($this->hydrateCriterionEvaluationResult)($criterionCode, $rawEvaluation['result']) : null,
        );
    }
}
