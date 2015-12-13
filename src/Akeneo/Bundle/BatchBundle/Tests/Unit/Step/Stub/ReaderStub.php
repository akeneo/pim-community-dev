<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step\Stub;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

class ReaderStub extends AbstractConfigurableStepElement implements ItemReaderInterface, StepExecutionAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function read()
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
