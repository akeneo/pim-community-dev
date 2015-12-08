<?php

namespace Akeneo\Bundle\BatchBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

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
