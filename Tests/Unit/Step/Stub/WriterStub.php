<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step\Stub;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

class WriterStub implements ItemWriterInterface, StepExecutionAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
    }
}
