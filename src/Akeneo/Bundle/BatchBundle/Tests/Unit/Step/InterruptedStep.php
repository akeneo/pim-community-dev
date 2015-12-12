<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;

/**
 * Step used for test and always declared a stopped execution
 *
 */
class InterruptedStep extends AbstractStep
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $execution)
    {
        $execution->setTerminateOnly();
    }
}
