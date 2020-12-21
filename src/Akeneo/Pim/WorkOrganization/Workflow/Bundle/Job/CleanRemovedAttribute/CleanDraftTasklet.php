<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Cleaner\RemovedAttributeCleaner;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanDraftTasklet implements TaskletInterface
{
    private RemovedAttributeCleaner $removedAttributeCleaner;

    public function __construct(
        RemovedAttributeCleaner $removedAttributeCleaner
    ) {
        $this->removedAttributeCleaner = $removedAttributeCleaner;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->removedAttributeCleaner->cleanAffectedDrafts();
    }
}
