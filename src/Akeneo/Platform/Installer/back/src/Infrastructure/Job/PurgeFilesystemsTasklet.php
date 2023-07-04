<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Job;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use League\Flysystem\FilesystemOperator;

final class PurgeFilesystemsTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    /**
     * @param iterable<FilesystemOperator> $filesystems
     */
    public function __construct(
        private readonly FilesystemPurgerInterface $filesystemPurger,
        private readonly iterable $filesystems,
        private readonly JobStopper $jobStopper,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(sprintf('In order to execute "%s" you need to set a step execution.', PurgeFilesystemsTasklet::class));
        }

        $currentState = $this->stepExecution->getCurrentState();

        foreach ($this->filesystems as $key => $filesystem) {
            if ($this->jobStopper->isPausing($this->stepExecution)) {
                $this->jobStopper->pause($this->stepExecution, $currentState);
                break;
            }

            if (!in_array($key, $currentState)) {
                $this->filesystemPurger->execute($filesystem);
                $currentState[] = $key;
            }
        }
    }
}