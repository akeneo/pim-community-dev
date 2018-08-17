<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\Database\JobInstanceWriter;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class JobInstanceWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution
    ){
        $this->beConstructedWith($bulkSaver, $bulkDetacher);

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobInstanceWriter::class);
    }

    function it_is_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_saves_and_updates_job_instances(
        $bulkSaver,
        $bulkDetacher,
        $stepExecution,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $bulkSaver->saveAll([$jobInstance1, $jobInstance2]);
        $bulkDetacher->detachAll([$jobInstance1, $jobInstance2]);

        $jobInstance1->getId()->willReturn(null);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $jobInstance2->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([$jobInstance1, $jobInstance2]);
    }
}
