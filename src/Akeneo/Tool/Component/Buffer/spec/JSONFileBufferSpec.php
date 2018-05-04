<?php

namespace spec\Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\BufferInterface;
use Akeneo\Tool\Component\Buffer\Exception\UnsupportedItemTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Exception\Prediction\FailedPredictionException;

class JSONFileBufferSpec extends ObjectBehavior
{
    function it_is_a_buffer()
    {
        $this->shouldImplement(BufferInterface::class);
    }

    function it_writes_and_reads_several_items_fifo_style()
    {
        $items = ['item_1', 'item_2', 'item_3'];
        foreach ($items as $item) {
            $this->write($item);
        }

        $readItems = [];
        foreach ($this->getWrappedObject() as $item) {
            $readItems[] = $item;
        }

        if ($items !== $readItems) {
            throw new FailedPredictionException(sprintf(
                'Expected items "%s", got "%s"',
                implode(', ', $items),
                implode(', ', $readItems)));
        }
    }

    function it_supports_only_scalar_and_array_items()
    {
        $this->write('scalar');
        $this->write(['scalar']);
        $this
        ->shouldThrow(UnsupportedItemTypeException::class)
        ->during('write', [new \stdClass()]);
    }

    function it_switches_correctly_between_write_and_read_mode()
    {
        $this->write('item_1');
        $this->write('item_2');

        foreach ($this->getWrappedObject() as $item) {
            // do stuff with read items
        }

        $this->write('item_3');

        $readItems = [];
        foreach ($this->getWrappedObject() as $item) {
            $readItems[] = $item;
        }

        $items = ['item_1', 'item_2', 'item_3'];
        if ($items !== $readItems) {
            throw new FailedPredictionException(sprintf(
                'Expected items "%s", got "%s"',
                implode(', ', $items),
                implode(', ', $readItems)));
        }
    }
}
