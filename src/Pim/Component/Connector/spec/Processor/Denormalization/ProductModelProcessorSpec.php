<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Pim\Component\Connector\Processor\Denormalization\ProductModelProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelProcessor::class);
    }
}
