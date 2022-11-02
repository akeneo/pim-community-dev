<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProjectsRecalculationTasklet implements TaskletInterface
{
    public function __construct(private ProjectsRecalculationLauncher $recalculationLauncher)
    {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
    }

    public function execute()
    {
        $this->recalculationLauncher->launch();
    }
}
