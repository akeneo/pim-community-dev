<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// @deprecated class, will be removed in 1.6
class ContextableCsvWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Writer\File\ContextableCsvWriter');
    }
}
