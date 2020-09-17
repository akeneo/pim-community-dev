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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class EvaluateProductModelsCriteriaTasklet implements TaskletInterface
{
    private const NB_PRODUCT_MODELS_MAX = 10000;
    private const BULK_SIZE = 100;

    /** @var StepExecution */
    private $stepExecution;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateAxesRates */
    private $consolidateAxisRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductModelsIdsToEvaluateQuery;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateAxisRates,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateAxisRates = $consolidateAxisRates;
        $this->getProductModelsIdsToEvaluateQuery = $getProductModelsIdsToEvaluateQuery;
    }

    public function execute(): void
    {
        foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::NB_PRODUCT_MODELS_MAX, self::BULK_SIZE) as $productModelIds) {
            $this->evaluatePendingCriteria->evaluateAllCriteria($productModelIds);

            $this->consolidateAxisRates->consolidate($productModelIds);

            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productModelIds));
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
