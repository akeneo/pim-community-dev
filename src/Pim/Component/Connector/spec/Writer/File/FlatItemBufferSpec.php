<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;

class FlatItemBufferSpec extends ObjectBehavior
{
    function it_is_a_buffer()
    {
        $this->shouldImplement('Akeneo\Component\Buffer\BufferInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\FlatItemBuffer');
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
