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
     * Default Lock TTL < 1 day = 23 hours. Can be overidden in child class
     */
    protected const LOCK_TTL_IN_SECONDS = 3600 * 23;

    protected StepExecution $stepExecution;

    public function __construct(protected LockFactory $lockFactory, protected string $jobCode)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public static function getLockIdentifier(string $jobCode): string
    {
        Assert::notEmpty($jobCode, 'The job code must not be empty');
        return sprintf('scheduled-job-%s', $jobCode);
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

    private function getLock(): LockInterface
    {
        $lockIdentifier = static::getLockIdentifier($this->jobCode);

        return $this->lockFactory->createLock($lockIdentifier, static::LOCK_TTL_IN_SECONDS);
    }
}
