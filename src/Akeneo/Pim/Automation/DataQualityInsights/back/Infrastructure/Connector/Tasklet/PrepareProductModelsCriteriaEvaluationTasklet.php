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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateMissingCriteriaEvaluationsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

class PrepareProductModelsCriteriaEvaluationTasklet implements TaskletInterface
{
    private const BULK_SIZE = 100;

    /** @var StepExecution */
    private $stepExecution;

    /** @var CreateMissingCriteriaEvaluationsInterface */
    private $createMissingCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    public function __construct(
        CreateMissingCriteriaEvaluationsInterface $createMissingCriteriaEvaluations,
        LoggerInterface $logger,
        CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository
    ) {
        $this->createMissingCriteriaEvaluations = $createMissingCriteriaEvaluations;
        $this->logger = $logger;
        $this->productModelCriterionEvaluationRepository = $productModelCriterionEvaluationRepository;
    }

    public function execute(): void
    {
        $this->cleanCriteriaOfDeletedProductModels();
        $this->createMissingCriteriaEvaluations();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $updatedSince = $this->updatedSince();
            $this->createMissingCriteriaEvaluations->createForProductsUpdatedSince($updatedSince, self::BULK_SIZE);
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

    private function updatedSince(): \DateTimeImmutable
    {
        $evaluateFrom = $this->stepExecution->getJobParameters()->get(PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER);

        return \DateTimeImmutable::createFromFormat(PrepareEvaluationsParameters::UPDATED_SINCE_DATE_FORMAT, $evaluateFrom);
    }

    private function cleanCriteriaOfDeletedProductModels()
    {
        $this->productModelCriterionEvaluationRepository->deleteUnknownProductsEvaluations();
    }
}
