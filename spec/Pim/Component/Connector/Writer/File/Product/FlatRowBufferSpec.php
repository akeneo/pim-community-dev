<?php

namespace spec\Pim\Component\Connector\Writer\File\Product;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlatRowBufferSpec extends ObjectBehavior
{
    function let(BufferFactory $bufferFactory, BufferInterface $buffer)
    {
        $bufferFactory->create()->willReturn($buffer);

        $this->beConstructedWith($bufferFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Product\FlatRowBuffer');
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
                'product' => [
                    'id' => 123,
                    'family' => 12,
                ],
                'media' => [
                    'filePath' => 'img/product1.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                'product' => [
                    'id' => 165,
                    'family' => 45,
                ],
                'media' => [
                    'filePath' => null,
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
        ], true);

        $this->getHeaders()->shouldReturn(['id', 'family']);
    }

    function it_has_a_buffer($buffer)
    {
        $this->getBuffer()->shouldReturn($buffer);
    }
}
