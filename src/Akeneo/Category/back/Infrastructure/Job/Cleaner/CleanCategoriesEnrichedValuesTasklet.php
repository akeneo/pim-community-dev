<?php

namespace Akeneo\Category\Infrastructure\Job\Cleaner;

use Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\CleanCategoryDataLinkedToChannel;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanCategoriesEnrichedValuesTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private readonly CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
    )
    {
    }

    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $channelCodes = $jobParameters->get('channel_codes');

        foreach ($channelCodes as $code) {
            ($this->cleanCategoryDataLinkedToChannel)($code);
        }
    }
}
