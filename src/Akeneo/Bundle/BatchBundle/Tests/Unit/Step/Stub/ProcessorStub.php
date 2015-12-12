<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step\Stub;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

class ProcessorStub extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process($item)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
    }

    public function getConfigurationFields()
    {
    }
}
