<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateCriteria
{
    public const NO_LIMIT = -1;

    public function __construct(
        private CriterionEvaluationRepositoryInterface $repository,
        private CriteriaEvaluationRegistry $evaluationRegistry,
        private GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        private LoggerInterface $logger,
        private string $productType
    ) {
    }

    /**
     * @param string[] $criteriaCodes
     */
    public function __invoke(ProductIdCollection $productIdCollection, array $criteriaCodes, callable $afterEvaluateCriterion = null): void
    {
        Assert::allInArray($criteriaCodes, array_map(fn (CriterionCode $code) => (string) $code, $this->evaluationRegistry->getCriterionCodes()));

        foreach ($productIdCollection as $productId) {
            $values = $this->getEvaluableProductValuesQuery->byProductId($productId);
            $criteriaEvaluation = $this->buildProductCriteriaEvaluation($productId, $criteriaCodes);

            if (0 === $values->count()) {
                return;
            }

            foreach ($criteriaEvaluation as $criterionEvaluation) {
                $startTime = microtime(true);

                $this->evaluateCriterion($criterionEvaluation, $values);

                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 3);
                if (null !== $afterEvaluateCriterion) {
                    $afterEvaluateCriterion($criterionEvaluation, $values, $startTime, $endTime, $duration);
                }
            }

            $this->repository->update($criteriaEvaluation);
        }
    }

    /**
     * @returns Write\CriterionEvaluationCollection
     */
    private function buildProductCriteriaEvaluation(ProductId $productId, array $criteriaCodes): Write\CriterionEvaluationCollection
    {
        $productCriteriaEvaluations = new Write\CriterionEvaluationCollection();

        foreach ($criteriaCodes as $criterionCode) {
            $productCriteriaEvaluations->add(new Write\CriterionEvaluation(
                new CriterionCode($criterionCode),
                $productId,
                CriterionEvaluationStatus::pending()
            ));
        }

        return $productCriteriaEvaluations;
    }

    private function evaluateCriterion(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): void
    {
        try {
            $evaluationService = $this->evaluationRegistry->get($criterionEvaluation->getCriterionCode());
            $criterionEvaluation->start();
            $result = $evaluationService->evaluate($criterionEvaluation, $productValues);
            $criterionEvaluation->end($result);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Failed to evaluate criterion {criterion_code} for product [{product_type}] with id {product_id}',
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getProductId(), 'product_type' => $this->productType, 'message' => $exception->getMessage()]
            );
            $criterionEvaluation->flagAsError();
        }
    }
}
