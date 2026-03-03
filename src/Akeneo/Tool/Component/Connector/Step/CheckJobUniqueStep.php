<?php

namespace Akeneo\Tool\Component\Connector\Step;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * Simple task to be executed from a TaskletStep.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckJobUniqueStep extends AbstractStep
{
    public function __construct(
        protected string $name,
        protected EventDispatcherInterface $eventDispatcher,
        protected JobRepositoryInterface $jobRepository,
        private readonly LoggerInterface $logger,
        private readonly JobExecutionManager $executionManager,
        private readonly JobExecutionRepository $jobExecutionRepository,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobCode = $stepExecution->getJobExecution()->getJobInstance()->getCode();
        Assert::notEmpty($jobCode, 'The job code must not be empty');

        $jobExecution = $stepExecution->getJobExecution();

        if ($this->jobExecutionRepository->isOtherJobExecutionRunning($stepExecution->getJobExecution())) {
            $this->logger->warning(
                'Cannot launch scheduled job because another execution is still running.',
                [
                    'job_code' => $jobCode,
                    'job_execution_id' => $jobExecution->getId(),
                ]
            );

            $jobExecution = $this->jobExecutionRepository->find($jobExecution->getId());
            $this->executionManager->markAsFailed($jobExecution);

            throw new JobInterruptedException(sprintf('Another instance of job %s is already running.', $jobCode));
        }
    }
}
