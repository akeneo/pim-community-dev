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

final class EvaluateProductsCriteriaTasklet implements TaskletInterface
{
    private const NB_PRODUCTS_MAX = 10000;
    private const BULK_SIZE = 100;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var StepExecution */
    private $stepExecution;

    /** @var ConsolidateAxesRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductIdsToEvaluateQuery;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
        $this->getProductIdsToEvaluateQuery = $getProductIdsToEvaluateQuery;
    }

    public function execute(): void
    {
        foreach ($this->getProductIdsToEvaluateQuery->execute(self::NB_PRODUCTS_MAX, self::BULK_SIZE) as $productIds) {
            $this->evaluatePendingCriteria->evaluateAllCriteria($productIds);

            $this->consolidateProductAxisRates->consolidate($productIds);

            $this->indexProductRates->execute($productIds);

            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productIds));
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
