<?php

namespace Pim\Bundle\BatchBundle\Step;

/**
 * Step instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
