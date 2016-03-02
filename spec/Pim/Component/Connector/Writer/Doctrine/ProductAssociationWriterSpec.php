<?php

namespace spec\Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;

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

    function it_is_a_configurable_step_execution_aware_writer()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
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
        $association1 = new Association();
        $product1->getAssociations()->willReturn(new ArrayCollection([$association1]));
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $product2->getId()->willReturn(42);
        $association2->getId()->willReturn(1);
        $product2->getAssociations()->willReturn(new ArrayCollection([$association2]));
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([$product1, $product2]);
    }
}
