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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

final class EvaluateProductsCriteriaTasklet implements TaskletInterface
{
    public const JOB_INSTANCE_NAME = 'data_quality_insights_evaluate_products_criteria';

    private const NB_PRODUCTS_MAX = 2500;
    private const BULK_SIZE = 100;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var StepExecution */
    private $stepExecution;

    /** @var ConsolidateProductAxisRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductIdsToEvaluateQuery;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductAxisRates $consolidateProductAxisRates,
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
