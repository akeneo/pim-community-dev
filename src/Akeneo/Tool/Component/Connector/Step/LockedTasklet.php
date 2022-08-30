<?php

namespace Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Webmozart\Assert\Assert;

/**
 * Simple task to be executed from a TaskletStep.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class LockedTasklet implements TaskletInterface
{
    /**
     * Job code must be re-defined in child class
     */
    protected const JOB_CODE = '';
    /**
     * Default Lock TTL = 1 day = 24 hours can be overidden
     */
    protected const LOCK_TTL_IN_SECONDS = 3600 * 24;

    protected StepExecution $stepExecution;

    public function __construct(protected LockFactory $lockFactory)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $lock = $this->getLock();

        if (!$lock->acquire()) {
            $this->lockedAbort();

            return;
        }

        try {
            $this->doExecute();
        } finally {
            $lock->release();
        }
    }

    /**
     * Action to do when the lock cannot be acquired
     */
    abstract protected function lockedAbort(): void;

    abstract protected function doExecute(): void;

    /**
     * Default lock identifier = job_code
     */
    protected function getLockIdentifier(): string
    {
        return static::JOB_CODE;
    }

    private function getLock(): LockInterface
    {
        Assert::notEmpty(static::JOB_CODE, 'The job code must not be empty');
        $lockIdentifier = $this->getLockIdentifier();

        return $this->lockFactory->createLock($lockIdentifier, static::LOCK_TTL_IN_SECONDS);
    }
}
