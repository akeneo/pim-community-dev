<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;

class FlatItemBufferSpec extends ObjectBehavior
{
    function it_is_a_buffer()
    {
        $this->shouldImplement(BufferInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FlatItemBuffer::class);
    }

    function it_writes_item_with_headers()
    {
        $this->write([
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ], ['withHeader' => true]);

        $this->getHeaders()->shouldReturn(['id', 'family']);
    }

    function it_counts_written_items_to_the_buffer()
    {
        $this->write([
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ]);

        $this->count()->shouldReturn(2);

        $this->write([
            [
                'id' => 456,
                'family' => 12,
            ],
        ]);

        $this->count()->shouldReturn(3);
    }
}
