<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Hydrator\hydrateCriterionEvaluationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductCriterionEvaluationByProductIdAndCriterionCodeQuery implements GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface
{
    public function __construct(
        private Connection                       $dbConnection,
        private hydrateCriterionEvaluationResult $hydrateCriterionEvaluationResult
    ) {
    }

    public function execute(ProductEntityIdInterface $productUuid, CriterionCode $criterionCode): ?Read\CriterionEvaluation
    {
        Assert::isInstanceOf($productUuid, ProductUuid::class);

        $query = <<<SQL
SELECT evaluated_at, status, result 
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid = :productUuid AND criterion_code = :criterionCode;
SQL;
        $rawEvaluation = $this->dbConnection->executeQuery(
            $query,
            ['productUuid' => $productUuid->toBytes(), 'criterionCode' => $criterionCode],
            ['productUuid' => Types::STRING, 'criterionCode' => Types::STRING],
        )->fetchAssociative();

        if (!$rawEvaluation) {
            return null;
        }

        return new Read\CriterionEvaluation(
            $criterionCode,
            $productUuid,
            null !== $rawEvaluation['evaluated_at'] ? Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($rawEvaluation['evaluated_at'], $this->dbConnection->getDatabasePlatform()) : null,
            new CriterionEvaluationStatus($rawEvaluation['status']),
            null !== $rawEvaluation['result'] ? ($this->hydrateCriterionEvaluationResult)($criterionCode, $rawEvaluation['result']) : null,
        );
    }
}
