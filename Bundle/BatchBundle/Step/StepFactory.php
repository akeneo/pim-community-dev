<?php

namespace Oro\Bundle\BatchBundle\Step;

/**
 * Step instance factory
 *
 */
class StepFactory
{
    protected $eventDispatcher;
    protected $jobRepository;

    public function __construct($eventDispatcher, $jobRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository   = $jobRepository;
    }

    public function createStep($title, $reader, $processor, $writer)
    {
        $step = new ItemStep($title);
        $step->setEventDispatcher($this->eventDispatcher);
        $step->setJobRepository($this->jobRepository);
        $step->setReader($reader);
        $step->setProcessor($processor);
        $step->setWriter($writer);

        return $step;
    }
}
