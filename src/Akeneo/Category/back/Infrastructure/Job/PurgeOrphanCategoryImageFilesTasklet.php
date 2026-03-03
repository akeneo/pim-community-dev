<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeOrphanCategoryImageFilesTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    protected const JOB_CODE = 'purge_orphan_category_image_files';

    public function __construct(
        private readonly JobStopper $jobStopper,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(sprintf('In order to execute "%s" you need to set a step execution.', PurgeOrphanCategoryImageFilesTasklet::class));
        }

        $iterator = $this->commandBus->dispatch(
            new PurgeOrphanCategoryImageFilesCommand(),
        );

        foreach ($iterator as $status) {
            if ($this->jobStopper->isStopping($this->stepExecution) || $this->jobStopper->isPausing($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);

                return;
            }
        }
    }
}
