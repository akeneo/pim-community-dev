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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateMissingCriteriaEvaluationsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluationsParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

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

    /** @var CreateMissingCriteriaEvaluationsInterface */
    private $createMissingProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        CreateMissingCriteriaEvaluationsInterface $createMissingProductsCriteriaEvaluations,
        LoggerInterface $logger,
        CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
        $this->getProductIdsToEvaluateQuery = $getProductIdsToEvaluateQuery;
        $this->createMissingProductsCriteriaEvaluations = $createMissingProductsCriteriaEvaluations;
        $this->logger = $logger;
        $this->productCriterionEvaluationRepository = $productCriterionEvaluationRepository;
    }

    public function execute(): void
    {
        $this->cleanCriteriaOfDeletedProducts();
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
            $updatedSince = $this->updatedSince();
            $this->createMissingProductsCriteriaEvaluations->createForProductsUpdatedSince($updatedSince, self::BULK_SIZE);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to create all missing criteria evaluations for the products',
                [
                    'error_code' => 'unable_to_create_missing_product_criteria_evaluation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }

    private function updatedSince(): \DateTimeImmutable
    {
        $evaluateFrom = $this->stepExecution->getJobParameters()->get(EvaluationsParameters::EVALUATE_FROM_FIELD);

        return \DateTimeImmutable::createFromFormat(EvaluationsParameters::EVALUATE_FROM_FORMAT, $evaluateFrom);
    }

    private function cleanCriteriaOfDeletedProducts()
    {
        $this->productCriterionEvaluationRepository->deleteUnknownProductsEvaluations();
    }
}
