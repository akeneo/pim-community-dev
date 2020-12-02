<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\CleanRemovedAttribute;

use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct()
    {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //TODO
    }
}
