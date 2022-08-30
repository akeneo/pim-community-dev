<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Job;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerInterface;
use Akeneo\Tool\Component\Connector\Step\LockedTasklet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;

/**
 * Purge version of entities
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeVersioning extends LockedTasklet
{
    protected const JOB_CODE = 'versioning_purge';

    public function __construct(
        protected LockFactory $lockFactory,
        private VersionPurgerInterface $versionPurger,
        private JobExecutionManager $executionManager,
        private JobExecutionRepository $jobExecutionRepository,
        private LoggerInterface $logger,
    ) {
        parent::__construct($this->lockFactory);
    }

    protected function getLockIdentifier(): string
    {
        return sprintf('scheduled-job-%s', static::JOB_CODE);
    }

    protected function lockedAbort(): void
    {
        $jobExecution = $this->stepExecution->getJobExecution();

        $this->logger->error(
            'Cannot launch scheduled job because another execution is still running.',
            [
                'job_code' => self::JOB_CODE,
                'job_execution_id' => $jobExecution->getId(),
            ]
        );

        //$this->executionManager->markAsFailed($this->stepExecution->getJobExecution());
        $jobExecution = $this->jobExecutionRepository->find($this->stepExecution->getJobExecution()->getId());
        $this->executionManager->markAsFailed($jobExecution);
    }

    protected function doExecute(): void
    {
        $purgeOptions['batch_size'] = (int)$this->stepExecution->getJobParameters()->get('batch-size');

        $moreThanDays = null !== $this->stepExecution->getJobParameters()->get('more-than-days') ?
            (int)$this->stepExecution->getJobParameters()->get('more-than-days') : null;
        $lessThanDays = null !== $this->stepExecution->getJobParameters()->get('less-than-days') ?
            (int)$this->stepExecution->getJobParameters()->get('less-than-days') : null;

        if (null !== $lessThanDays || null !== $moreThanDays) {
            $purgeOptions['days_number'] = $lessThanDays ?: $moreThanDays;
        }
        $purgeOptions['date_operator'] = null !== $lessThanDays ? '>' : '<';
        $purgeOptions['resource_name'] = $this->stepExecution->getJobParameters()->get('entity');

        $this->versionPurger->purge($purgeOptions);
    }
}
