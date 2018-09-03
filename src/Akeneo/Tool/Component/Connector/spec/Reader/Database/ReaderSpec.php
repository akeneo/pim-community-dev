<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class ReaderSpec extends ObjectBehavior
{
    function let(ObjectRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_returns_a_variation(
        $repository,
        ProductInterface $product,
        StepExecution $stepExecution
    ) {
        $repository->findAll()->willReturn([$product]);
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($product);
        $this->read()->shouldReturn(null);
    }
}
