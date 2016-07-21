<?php

namespace Akeneo\Component\Batch\Step;

use Akeneo\Component\Batch\Model\StepExecution;

/**
 * Classes that implement this interface have to receive the StepExecution.
 *
 * @api
 */
interface StepExecutionAwareInterface
{
    /**
     * @param StepExecution $stepExecution
     *
     * @api
     */
    public function setStepExecution(StepExecution $stepExecution);
}
