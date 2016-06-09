<?php

namespace spec\PimEnterprise\Component\Security\Connector\Writer;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\JobProfileAccess;

class AccessesWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($bulkSaver, $bulkDetacher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Security\Connector\Writer\AccessesWriter');
    }

    function it_is_a_writer()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Writer\Database\BaseWriter');
    }

    function it_massively_insert_and_update_objects(
        $bulkSaver,
        $bulkDetacher,
        $stepExecution,
        JobProfileAccess $object1,
        JobProfileAccess $object2
    ) {
        $bulkSaver->saveAll([$object1, $object2]);
        $bulkDetacher->detachAll([$object1, $object2]);

        $object1->getId()->willReturn(null);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $object2->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([[$object1, $object2]]);
    }
}
