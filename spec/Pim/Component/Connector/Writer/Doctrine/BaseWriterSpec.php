<?php

namespace spec\Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

class BaseWriterSpec extends ObjectBehavior
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
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_massively_insert_and_update_objects(
        $bulkSaver,
        $bulkDetacher,
        $stepExecution,
        CategoryInterface $object1,
        CategoryInterface $object2
    ) {
        $bulkSaver->saveAll([$object1, $object2]);
        $bulkDetacher->detachAll([$object1, $object2]);

        $object1->getId()->willReturn(null);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $object2->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([$object1, $object2]);
    }
}
