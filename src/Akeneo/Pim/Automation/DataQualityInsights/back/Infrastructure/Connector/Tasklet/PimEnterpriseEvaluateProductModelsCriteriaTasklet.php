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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateMissingCriteriaEvaluationsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

class PimEnterpriseEvaluateProductModelsCriteriaTasklet implements TaskletInterface
{
    use EvaluationJobAwareTrait;

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

    /** @var CreateMissingCriteriaEvaluationsInterface */
    private $createMissingCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateAxisRates,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        CreateMissingCriteriaEvaluationsInterface $createMissingCriteriaEvaluations,
        LoggerInterface $logger,
        CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateAxisRates = $consolidateAxisRates;
        $this->getProductModelsIdsToEvaluateQuery = $getProductModelsIdsToEvaluateQuery;
        $this->createMissingCriteriaEvaluations = $createMissingCriteriaEvaluations;
        $this->logger = $logger;
        $this->productModelCriterionEvaluationRepository = $productModelCriterionEvaluationRepository;
    }

    public function execute(): void
    {
        $this->cleanCriteriaOfDeletedProductModels();
        $this->createMissingCriteriaEvaluations();

        foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::NB_PRODUCT_MODELS_MAX, self::BULK_SIZE) as $productModelIds) {
            $this->evaluatePendingCriteria->evaluateAllCriteria($productModelIds);

            $this->consolidateAxisRates->consolidate($productModelIds);

            $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($productModelIds));
        }
    }

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $updatedSince = $this->updatedSince();
            $this->createMissingCriteriaEvaluations->createForProductsUpdatedSince($updatedSince, self::BULK_SIZE);
            $this->createMissingCriteriaEvaluations->createForProductsImpactedByStructureUpdatedSince($updatedSince, self::BULK_SIZE);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to create all missing criteria evaluations for the product models',
                [
                    'error_code' => 'unable_to_create_missing_product_model_criteria_evaluation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }

    private function cleanCriteriaOfDeletedProductModels()
    {
        $this->productModelCriterionEvaluationRepository->deleteUnknownProductsEvaluations();
    }
}
