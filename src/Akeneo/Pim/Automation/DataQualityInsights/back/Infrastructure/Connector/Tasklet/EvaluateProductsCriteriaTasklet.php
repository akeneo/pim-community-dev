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
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateMissingProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class EvaluateProductsCriteriaTasklet implements TaskletInterface
{
    public const JOB_INSTANCE_NAME = 'data_quality_insights_evaluate_products_criteria';

    private const NB_PRODUCTS_MAX = 5000;
    private const BULK_SIZE = 100;
    private const PRODUCTS_UPDATED_SINCE = '-1 DAY';

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

    /** @var CreateMissingProductsCriteriaEvaluations */
    private $createMissingProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductAxisRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        CreateMissingProductsCriteriaEvaluations $createMissingProductsCriteriaEvaluations,
        LoggerInterface $logger
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
        $this->getProductIdsToEvaluateQuery = $getProductIdsToEvaluateQuery;
        $this->createMissingProductsCriteriaEvaluations = $createMissingProductsCriteriaEvaluations;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->createMissingCriteriaEvaluations();

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

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $this->createMissingProductsCriteriaEvaluations->createForProductsUpdatedSince(
                new \DateTimeImmutable(self::PRODUCTS_UPDATED_SINCE),
                self::BULK_SIZE
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to create all missing criteria evaluations',
                [
                    'error_code' => 'unable_to_create_missing_product_criteria_evaluation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }
}
