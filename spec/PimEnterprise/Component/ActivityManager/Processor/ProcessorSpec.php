<?php

namespace spec\Akeneo\ActivityManager\Component\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;

class ProcessorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(AbstractProcessor::class);
    }
}
