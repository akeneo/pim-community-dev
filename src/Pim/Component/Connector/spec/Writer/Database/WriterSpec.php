<?php

namespace spec\Pim\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;

class WriterSpec extends ObjectBehavior
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
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
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
