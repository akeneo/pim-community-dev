<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateProductsAndProductModelsCriteriaTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution;

    public function __construct(
        private GetEntityIdsToEvaluateQueryInterface $getProductUuidsToEvaluateQuery,
        private GetEntityIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        private EvaluateProducts $evaluateProducts,
        private EvaluateProductModels $evaluateProductModels,
        private int $limitPerLoop = 1000,
        private int $bulkSize = 100,
        private int $timeBoxInSecondsAllowed = 1700, //~28 minutes
        private int $noEvaluationSleep = 60,
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
                $evaluationTime['products']['time'] += round($evaluationProductsEndTime - $evaluationProductsStartTime, 3);
                $this->stepExecution->addSummaryInfo('evaluations', $evaluationTime);
            }

            if ($continueToEvaluateProductModels) {
                $evaluationProductModelsStartTime = microtime(true);
                $evaluationCount = $this->evaluatePendingProductModelCriteria();
                $evaluationProductModelsEndTime = microtime(true);
                $continueToEvaluateProductModels = $evaluationCount > 0;

                $evaluationTime['product_models']['count'] += $evaluationCount;
                $evaluationTime['product_models']['time'] += round($evaluationProductModelsEndTime - $evaluationProductModelsStartTime, 3);
                $this->stepExecution->addSummaryInfo('evaluations', $evaluationTime);
            }

            if ($continueToEvaluateProducts === false && $continueToEvaluateProductModels === false) {
                sleep($this->noEvaluationSleep);
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

        foreach ($this->getProductUuidsToEvaluateQuery->execute($this->limitPerLoop, $this->bulkSize) as $productUuidCollection) {
            Assert::isInstanceOf($productUuidCollection, ProductUuidCollection::class);
            ($this->evaluateProducts)($productUuidCollection);

            $evaluationCount += count($productUuidCollection);
            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productUuidCollection));
        }

        return $evaluationCount;
    }

    private function evaluatePendingProductModelCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductModelsIdsToEvaluateQuery->execute($this->limitPerLoop, $this->bulkSize) as $productModelIdCollection) {
            Assert::isInstanceOf($productModelIdCollection, ProductModelIdCollection::class);
            ($this->evaluateProductModels)($productModelIdCollection);

            $evaluationCount += count($productModelIdCollection);
            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productModelIdCollection));
        }

        return $evaluationCount;
    }

    private function isTimeboxReached(int $startTime): bool
    {
        $actualTime = time();
        $timeSpentFromBegining = $actualTime - $startTime;

        return $timeSpentFromBegining >= $this->timeBoxInSecondsAllowed;
    }
}
