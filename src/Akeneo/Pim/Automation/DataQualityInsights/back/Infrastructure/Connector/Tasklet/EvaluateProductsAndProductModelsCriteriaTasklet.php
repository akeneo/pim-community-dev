<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateProductsAndProductModelsCriteriaTasklet implements TaskletInterface
{
    private const LIMIT_PER_LOOP = 1000;
    private const BULK_SIZE = 100;
    private const TIMEBOX_IN_SECONDS_ALLOWED = 1700; // ~28 minutes

    private ?StepExecution $stepExecution;

    public function __construct(
        private GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        private GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        private EvaluateProducts $evaluateProducts,
        private EvaluateProductModels $evaluateProductModels
    ) {
    }

    public function execute(): void
    {
        $continueToEvaluateProducts = true;
        $continueToEvaluateProductModels = true;
        $startTime = time();
        $evaluationTime['products'] = [
            'count' => 0,
            'time' => 0
        ];
        $evaluationTime['product_models'] = [
            'count' => 0,
            'time' => 0
        ];

        do {
            if ($continueToEvaluateProducts) {
                $evaluationProductsStartTime = microtime(true);
                $evaluationCount = $this->evaluatePendingProductCriteria();
                $evaluationProductsEndTime = microtime(true);
                $continueToEvaluateProducts = $evaluationCount > 0;

                $evaluationTime['products']['count'] += $evaluationCount;
                $evaluationTime['products']['time'] += round($evaluationProductsEndTime - $evaluationProductsStartTime, 2);
            }

            if ($continueToEvaluateProductModels) {
                $evaluationProductModelsStartTime = microtime(true);
                $evaluationCount = $this->evaluatePendingProductModelCriteria();
                $evaluationProductModelsEndTime = microtime(true);
                $continueToEvaluateProductModels = $evaluationCount > 0;

                $evaluationTime['product_models']['count'] += $evaluationCount;
                $evaluationTime['product_models']['time'] += round($evaluationProductModelsEndTime - $evaluationProductModelsStartTime, 2);
            }

            if ($continueToEvaluateProducts === false && $continueToEvaluateProductModels === false) {
                sleep(60);
                $continueToEvaluateProducts = true;
                $continueToEvaluateProductModels = true;
            }
        } while ($this->isTimeboxReached($startTime) === false);

        $this->stepExecution->setTrackingData(array_merge($this->stepExecution->getTrackingData(), ['evaluations' => $evaluationTime]));
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function evaluatePendingProductCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productIds) {
            ($this->evaluateProducts)($productIds);

            $evaluationCount += count($productIds);
            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productIds));
        }

        return $evaluationCount;
    }

    private function evaluatePendingProductModelCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productModelIds) {
            ($this->evaluateProductModels)($productModelIds);

            $evaluationCount += count($productModelIds);
            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productModelIds));
        }

        return $evaluationCount;
    }

    private function isTimeboxReached(int $startTime): bool
    {
        $actualTime = time();
        $timeSpentFromBegining = $actualTime - $startTime;

        if ($timeSpentFromBegining >= self::TIMEBOX_IN_SECONDS_ALLOWED) {
            return true;
        }

        return false;
    }
}
