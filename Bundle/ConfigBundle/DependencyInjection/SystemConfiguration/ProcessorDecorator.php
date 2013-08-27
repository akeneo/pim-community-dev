<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration;

use Symfony\Component\Config\Definition\Processor;

class ProcessorDecorator
{
    protected $processor;

    protected function getProcessor()
    {
        return $this->processor?: new Processor();
    }

    protected function
}
