<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

final class PimEnterpriseGetCriteriaEvaluationsByProductUuidQuery implements GetCriteriaEvaluationsByEntityIdQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsByProductUuidQuery
    ) {
    }

    public function execute(ProductEntityIdInterface $productId): Read\CriterionEvaluationCollection
    {
        Assert::isInstanceOf($productId, ProductUuid::class);

        $productCriteriaEvaluations = $this->getCriteriaEvaluationsByProductUuidQuery->execute($productId);
        $attributeSpellingResult = $this->getAttributeSpellingResultFromProductFamily($productId);

        if (null === $attributeSpellingResult) {
            return $productCriteriaEvaluations;
        }

        $completeCriterionEvaluations = new Read\CriterionEvaluationCollection();

        foreach ($productCriteriaEvaluations as $criterionEvaluation) {
            if (EvaluateAttributeSpelling::CRITERION_CODE === (string)$criterionEvaluation->getCriterionCode()) {
                $criterionEvaluation = new Read\CriterionEvaluation(
                    $criterionEvaluation->getCriterionCode(),
                    $criterionEvaluation->getProductId(),
                    $criterionEvaluation->getEvaluatedAt(),
                    $criterionEvaluation->getStatus(),
                    $attributeSpellingResult
                );
            }
            $completeCriterionEvaluations->add($criterionEvaluation);
        }

        return $completeCriterionEvaluations;
    }

    private function getAttributeSpellingResultFromProductFamily(ProductUuid $productUuid): ?Read\CriterionEvaluationResult
    {
        $query = <<<SQL
SELECT family_evaluation.result
FROM pim_catalog_product AS product
    INNER JOIN pimee_dqi_family_criteria_evaluation AS family_evaluation
        ON family_evaluation.family_id = product.family_id
        AND family_evaluation.criterion_code = :criterionCode
WHERE product.uuid = :productUuid;
SQL;

        $rawResult = $this->dbConnection->executeQuery(
            $query,
            ['productUuid' => $productUuid->toBytes(), 'criterionCode' => EvaluateAttributeSpelling::CRITERION_CODE],
            ['productUuid' => \PDO::PARAM_STR]
        )->fetchOne();

        if (empty($rawResult)) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
