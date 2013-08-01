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
    protected $logger;
    protected $jobRepository;

    public function __construct($logger, $jobRepository)
    {
        $this->logger        = $logger;
        $this->jobRepository = $jobRepository;
    }

    public function createStep($title, $reader, $processor, $writer)
    {
        $step = new ItemStep($title);
        $step->setLogger($this->logger);
        $step->setJobRepository($this->jobRepository);
        $step->setReader($reader);
        $step->setProcessor($processor);
        $step->setWriter($writer);

        return $step;
    }
}
