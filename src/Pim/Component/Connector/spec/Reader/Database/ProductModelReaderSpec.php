<?php

namespace spec\Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Promise\ReturnPromise;

class ProductModelReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($pqbFactory);

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_reads_product_models(
        $pqbFactory,
        $stepExecution,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3
    ) {
        $pqbFactory->create([])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $productModels = [$productModel1, $productModel2, $productModel3];
        $productModelsCount = count($productModels);
        $cursor->valid()->will(
            function () use (&$productModelsCount) {
                return $productModelsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($productModels));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);

        $this->initialize();
        $this->read()->shouldReturn($productModel1);
        $this->read()->shouldReturn($productModel2);
        $this->read()->shouldReturn($productModel3);
        $this->read()->shouldReturn(null);
    }
}
