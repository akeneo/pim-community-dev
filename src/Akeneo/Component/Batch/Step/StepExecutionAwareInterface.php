<?php

namespace Akeneo\Component\Batch\Step;

use Akeneo\Component\Batch\Model\StepExecution;

/**
 * Interface is used to receive StepExecution instance inside reader, processor or writer
 */
interface StepExecutionAwareInterface
{
    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution);
}
