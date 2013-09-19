<?php

namespace Oro\Bundle\ImportExportBundle\Context;

use Oro\Bundle\BatchBundle\Entity\StepExecution;

class ContextRegistry
{
    /**
     * @var array
     */
    protected $contexts = array();

    /**
     * @param StepExecution $stepExecution
     * @return mixed
     */
    public function getByStepExecution(StepExecution $stepExecution)
    {
        $key = spl_object_hash($stepExecution);

        if (empty($this->contexts[$key])) {
            $this->contexts[$key] = $this->createByStepExecution($stepExecution);
        }

        return $this->contexts[$key];
    }

    /**
     * @param StepExecution $stepExecution
     * @return ImportExportProxyContext
     */
    protected function createByStepExecution(StepExecution $stepExecution)
    {
        return new StepExecutionProxyContext($stepExecution);
    }
}
