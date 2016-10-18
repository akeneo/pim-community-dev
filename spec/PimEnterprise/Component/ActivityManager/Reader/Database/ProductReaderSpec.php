<?php

namespace spec\Akeneo\ActivityManager\Component\Database\Reader;

use Akeneo\ActivityManager\Component\Reader\Database\ProductReader;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

class ProductReaderSpec extends ObjectBehavior
{
    function let(InitializableInterface $productReader)
    {
        $this->beConstructedWith($productReader);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductReader::class);
    }

    function it_a_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }
}
