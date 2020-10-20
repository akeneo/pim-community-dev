<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveEvaluationsWithoutProductTasklet implements TaskletInterface
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(
        CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository,
        CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository,
        LoggerInterface $logger
    ) {
        $this->productCriterionEvaluationRepository = $productCriterionEvaluationRepository;
        $this->productModelCriterionEvaluationRepository = $productModelCriterionEvaluationRepository;
        $this->logger = $logger;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        try {
            $this->productCriterionEvaluationRepository->deleteUnknownProductsEvaluations();
            $this->productModelCriterionEvaluationRepository->deleteUnknownProductsEvaluations();
        } catch (\Throwable $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Remove evaluations without product failed.', [
                'step_execution_id' => $this->stepExecution->getId(),
                'message' => $exception->getMessage()
            ]);
        }
    }
}
