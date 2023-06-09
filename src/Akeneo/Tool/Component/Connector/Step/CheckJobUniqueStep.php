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
use Symfony\Component\Lock\LockFactory;
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
    /**
     * Default Lock TTL < 1 day = 23 hours. Can be overidden in child class
     */
    protected const LOCK_TTL_IN_SECONDS = 3600 * 23;

    public function __construct(
        protected string $name,
        protected EventDispatcherInterface $eventDispatcher,
        protected JobRepositoryInterface $jobRepository,
        protected LockFactory $lockFactory,
        private LoggerInterface $logger,
        private JobExecutionManager $executionManager,
        private JobExecutionRepository $jobExecutionRepository,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobCode = $stepExecution->getJobExecution()->getJobInstance()->getCode();
        Assert::notEmpty($jobCode, 'The job code must not be empty');

        $lockIdentifier = sprintf('scheduled-job-%s', $jobCode);
        $lock = $this->lockFactory->createLock($lockIdentifier, static::LOCK_TTL_IN_SECONDS, false);

        $jobExecution = $stepExecution->getJobExecution();

        if (!$lock->acquire()) {
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

        $this->logger->notice(
            sprintf('Lock %s acquired', $lockIdentifier),
            [
                'job_code' => $jobCode,
                'job_execution_id' => $jobExecution->getId(),
            ]
        );
    }
}
