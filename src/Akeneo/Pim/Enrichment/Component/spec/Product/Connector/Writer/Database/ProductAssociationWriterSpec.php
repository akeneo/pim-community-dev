<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

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
        AssociationInterface $association2
    ) {
        $bulkSaver->saveAll([$product1, $product2]);
        $bulkDetacher->detachAll([$product1, $product2]);

        $product1->getId()->willReturn(null);
        $association1 = new ProductAssociation();
        $product1->getAssociations()->willReturn(new ArrayCollection([$association1]));
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $product2->getId()->willReturn(42);
        $association2->getId()->willReturn(1);
        $product2->getAssociations()->willReturn(new ArrayCollection([$association2]));
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([$product1, $product2]);
    }
}
