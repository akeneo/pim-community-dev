<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

class ProductAssociationWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($bulkSaver, $bulkDetacher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_massively_insert_and_update_objects(
        $bulkSaver,
        $bulkDetacher,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        Collection $associationCollection1,
        Collection $associationCollection2,
        \Iterator $associationIterator1,
        \Iterator $associationIterator2,
        AssociationInterface $association1,
        AssociationInterface $association2,
        GroupInterface $group,
        ProductModelInterface $productModel
    ) {

        $product1->getId()->willReturn(null);
        $product1->getAssociations()->willReturn($associationCollection1);

        $associationCollection1->getIterator()->willReturn($associationIterator1);
        $associationIterator1->valid()->willReturn(true, false);
        $associationIterator1->current()->willReturn($association1);
        $associationIterator1->next()->shouldBeCalled();
        $associationIterator1->rewind()->shouldBeCalled();

        $association1->getProducts()->willReturn(new ArrayCollection([$product2]));
        $association1->getProductModels()->willReturn(new ArrayCollection([$productModel]));
        $association1->getGroups()->willReturn(new ArrayCollection([$group]));
        $association1->getId()->willReturn(1);

        $product2->getId()->willReturn(42);
        $product2->getAssociations()->willReturn($associationCollection2);

        $associationCollection2->getIterator()->willReturn($associationIterator2);
        $associationIterator2->valid()->willReturn(true, false);
        $associationIterator2->current()->willReturn($association2);
        $associationIterator2->next()->shouldBeCalled();
        $associationIterator2->rewind()->shouldBeCalled();

        $association2->getProducts()->willReturn(new ArrayCollection([$product1]));
        $association2->getProductModels()->willReturn(new ArrayCollection([$productModel]));
        $association2->getGroups()->willReturn(new ArrayCollection([$group]));
        $association2->getId()->willReturn(2);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(6);
        $bulkSaver->saveAll([$product1, $product2])->shouldBeCalled();
        $bulkDetacher->detachAll([$product1, $product2])->shouldBeCalled();

        $this->write([$product1, $product2]);
    }
}
