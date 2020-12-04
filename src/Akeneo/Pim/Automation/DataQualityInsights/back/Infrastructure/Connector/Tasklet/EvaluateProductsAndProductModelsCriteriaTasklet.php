<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;
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

    private EvaluatePendingCriteria $evaluatePendingProductCriteria;

    private ConsolidateAxesRates $consolidateProductAxisRates;

    private UpdateProductsIndex $updateProductsIndex;

    private GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery;

    private EvaluatePendingCriteria $evaluatePendingProductModelCriteria;

    private ConsolidateAxesRates $consolidateProductModelAxisRates;

    private GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingProductCriteria,
        ConsolidateAxesRates $consolidateProductAxisRates,
        UpdateProductsIndex $updateProductsIndex,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        EvaluatePendingCriteria $evaluatePendingProductModelCriteria,
        ConsolidateAxesRates $consolidateProductModelAxisRates,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery
    ) {
        $this->evaluatePendingProductCriteria = $evaluatePendingProductCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->updateProductsIndex = $updateProductsIndex;
        $this->getProductIdsToEvaluateQuery = $getProductIdsToEvaluateQuery;
        $this->evaluatePendingProductModelCriteria = $evaluatePendingProductModelCriteria;
        $this->consolidateProductModelAxisRates = $consolidateProductModelAxisRates;
        $this->getProductModelsIdsToEvaluateQuery = $getProductModelsIdsToEvaluateQuery;
    }

    public function execute(): void
    {
        $continueToEvaluateProducts = true;
        $continueToEvaluateProductModels = true;
        $startTime = time();

        do {
            if ($continueToEvaluateProducts) {
                $evaluationCount = $this->evaluatePendingProductCriteria();
                $continueToEvaluateProducts = $evaluationCount > 0;
            }

            if ($continueToEvaluateProductModels) {
                $evaluationCount = $this->evaluatePendingProductModelCriteria();
                $continueToEvaluateProductModels = $evaluationCount > 0;
            }

            if ($continueToEvaluateProducts === false && $continueToEvaluateProductModels === false) {
                sleep(60);
                $continueToEvaluateProducts = true;
                $continueToEvaluateProductModels = true;
            }
        } while ($this->isTimeboxReached($startTime) === false);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function evaluatePendingProductCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productIds) {
            $this->evaluatePendingProductCriteria->evaluateAllCriteria($productIds);

            $this->consolidateProductAxisRates->consolidate($productIds);

            $this->updateProductsIndex->execute($productIds);

            $evaluationCount += count($productIds);
            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productIds));
        }

        return $evaluationCount;
    }

    private function evaluatePendingProductModelCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productModelIds) {
            $this->evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIds);

            $this->consolidateProductModelAxisRates->consolidate($productModelIds);

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
