<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

final class EvaluateProductsAndProductModelsCriteriaTasklet implements TaskletInterface
{
    private const LIMIT_PER_LOOP = 1000;
    private const BULK_SIZE = 100;
    private const TIMEBOX_IN_SECONDS_ALLOWED = 1700; // ~28 minutes

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingProductCriteria;

    /** @var StepExecution */
    private $stepExecution;

    /** @var ConsolidateAxesRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductIdsToEvaluateQuery;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingProductModelCriteria;

    /** @var ConsolidateAxesRates */
    private $consolidateProductModelAxisRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductModelsIdsToEvaluateQuery;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingProductCriteria,
        ConsolidateAxesRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        EvaluatePendingCriteria $evaluatePendingProductModelCriteria,
        ConsolidateAxesRates $consolidateProductModelAxisRates,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery
    ) {
        $this->evaluatePendingProductCriteria = $evaluatePendingProductCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
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

            if (!$continueToEvaluateProducts && !$continueToEvaluateProductModels) {
                sleep(60);
                $continueToEvaluateProducts = true;
                $continueToEvaluateProductModels = true;
            }
        } while (!$this->isTimeboxReached($startTime));
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function evaluatePendingProductCriteria(): int
    {
        $evaluationCount = 0;
        foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productIds) {
            $this->evaluatePendingProductCriteria->evaluateAllCriteria($productIds);

            $this->consolidateProductAxisRates->consolidate($productIds);

            $this->indexProductRates->execute($productIds);

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
        return $timeSpentFromBegining >= self::TIMEBOX_IN_SECONDS_ALLOWED;
    }
}
