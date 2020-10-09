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
        $startTime = time();

        while ($this->isTimeboxReached($startTime) === false) {
            $this->evaluatePendingProductCriteria();
            $this->evaluatePendingProductModelCriteria();
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function evaluatePendingProductCriteria(): void
    {
        foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productIds) {
            $this->evaluatePendingProductCriteria->evaluateAllCriteria($productIds);

            $this->consolidateProductAxisRates->consolidate($productIds);

            $this->indexProductRates->execute($productIds);

            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productIds));
        }
    }

    private function evaluatePendingProductModelCriteria(): void
    {
        foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, self::BULK_SIZE) as $productModelIds) {
            $this->evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIds);

            $this->consolidateProductModelAxisRates->consolidate($productModelIds);

            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productModelIds));
        }
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
