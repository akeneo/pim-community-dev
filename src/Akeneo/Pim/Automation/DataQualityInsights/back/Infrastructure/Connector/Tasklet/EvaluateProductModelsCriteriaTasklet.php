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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateMissingCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

class EvaluateProductModelsCriteriaTasklet implements TaskletInterface
{
    private const NB_PRODUCT_MODELS_MAX = 5000;
    private const BULK_SIZE = 100;
    private const PRODUCT_MODELS_UPDATED_SINCE = '-1 DAY';

    /** @var StepExecution */
    private $stepExecution;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateAxesRates */
    private $consolidateAxisRates;

    /** @var GetProductIdsToEvaluateQueryInterface */
    private $getProductModelsIdsToEvaluateQuery;

    /** @var CreateMissingCriteriaEvaluations */
    private $createMissingCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateAxisRates,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        CreateMissingCriteriaEvaluations $createMissingCriteriaEvaluations,
        LoggerInterface $logger
    ) {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateAxisRates = $consolidateAxisRates;
        $this->getProductModelsIdsToEvaluateQuery = $getProductModelsIdsToEvaluateQuery;
        $this->createMissingCriteriaEvaluations = $createMissingCriteriaEvaluations;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->createMissingCriteriaEvaluations();

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

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $this->createMissingCriteriaEvaluations->createForProductsUpdatedSince(
                new \DateTimeImmutable(self::PRODUCT_MODELS_UPDATED_SINCE),
                self::BULK_SIZE
            );
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
}
