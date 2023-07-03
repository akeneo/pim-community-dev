<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Job;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

final class PurgeFilesystemsTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly FilesystemPurgerInterface $filesystemPurger,
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

        $this->filesystemPurger->execute();
    }
}