<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlatItemBufferSpec extends ObjectBehavior
{
    function let(BufferFactory $bufferFactory, BufferInterface $buffer)
    {
        $bufferFactory->create()->willReturn($buffer);

        $this->beConstructedWith($bufferFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\FlatItemBuffer');
    }

    function it_writes_item_to_the_buffer($buffer)
    {
        $buffer->write([
            'id' => 123,
            'family' => 12,
        ])->shouldbeCalled();

        $buffer->write([
            'id' => 165,
            'family' => 45,
        ])->shouldbeCalled();

        $this->write([
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ], true);

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
        ], true);

        $this->count()->shouldReturn(2);

        $this->write([
            [
                'id' => 456,
                'family' => 12,
            ],
        ], true);

        $this->count()->shouldReturn(3);
    }

    function it_has_a_buffer($buffer)
    {
        $this->getBuffer()->shouldReturn($buffer);
    }
}
