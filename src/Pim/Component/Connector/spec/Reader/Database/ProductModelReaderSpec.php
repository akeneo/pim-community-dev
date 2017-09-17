<?php

namespace spec\Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class ProductModelReaderSpec extends ObjectBehavior
{
    function let(ProductModelRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_returns_a_product_model(
        $repository,
        ProductModelInterface $productModel,
        StepExecution $stepExecution
    ) {
        $repository->findAll()->willReturn([$productModel]);
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($productModel);
        $this->read()->shouldReturn(null);
    }
}
