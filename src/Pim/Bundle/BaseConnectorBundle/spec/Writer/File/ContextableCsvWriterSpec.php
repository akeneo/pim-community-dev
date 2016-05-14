<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// * TODO TIP-303: deprecated class and spec to drop
class ContextableCsvWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Writer\File\ContextableCsvWriter');
    }
}
