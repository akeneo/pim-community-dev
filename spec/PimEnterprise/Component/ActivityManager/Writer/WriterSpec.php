<?php

namespace spec\Akeneo\ActivityManager\Component\Processor;

use Akeneo\ActivityManager\Component\Writer\Writer;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use PhpSpec\ObjectBehavior;

class WriterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Writer::class);
    }

    function it_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }
}
