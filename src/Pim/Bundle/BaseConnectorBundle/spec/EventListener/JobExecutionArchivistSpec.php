<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\EventListener;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemWriterRegistry;

class JobExecutionArchivistSpec extends ObjectBehavior
{
    function let(InvalidItemWriterRegistry $invalidItemWriterRegistry)
    {
        $this->beConstructedWith($invalidItemWriterRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_subscribed_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeStatusUpgrade'
            ]
        );
    }

    function it_throws_an_exception_if_there_is_already_a_registered_archier(ArchiverInterface $archiver)
    {
        $archiver->getName()->willReturn('output');

        $this->registerArchiver($archiver);
        $this->shouldThrow('\InvalidArgumentException')->during('registerArchiver', [$archiver]);
    }

    function it_returns_generated_archives(
        $invalidItemWriterRegistry,
        JobExecution $jobExecution,
        ArchiverInterface $archiver,
        ArchiverInterface $archiver2
    ) {
        $invalidItemWriterRegistry->getWriters()->willReturn([]);
        $jobExecution->isRunning()->willReturn(false);

        $archiver->getName()->willReturn('output');
        $archiver->getArchives($jobExecution)->willReturn(['a', 'b']);
        $this->registerArchiver($archiver);

        $archiver2->getName()->willReturn('input');
        $archiver2->getArchives($jobExecution)->willReturn(['a', 'b']);
        $this->registerArchiver($archiver2);

        $this->getArchives($jobExecution)->shouldReturn(['output' => ['a', 'b'], 'input' => ['a', 'b']]);
    }

    function it_get_archive_from_invalid_items_if_no_archiver_is_defined(
        $invalidItemWriterRegistry,
        JobExecution $jobExecution,
        ArchiverInterface $archiver
    ) {
        $invalidItemWriterRegistry->getWriter('archiver_name')->willReturn($archiver);
        $archiver->getArchive($jobExecution, 'key')->willReturn('expected_archive');

        $this->getArchive($jobExecution, 'archiver_name', 'key')->shouldReturn('expected_archive');
    }

    function it_returns_the_corresponding_archiver(JobExecution $jobExecution, ArchiverInterface $archiver)
    {
        $archiver->getName()->willReturn('output');
        $archiver->getArchive($jobExecution, 'key')->shouldBeCalled();
        $this->registerArchiver($archiver);

        $this->getArchive($jobExecution, 'output', 'key');
    }

    function it_register_an_event_and_verify_if_job_is_supported(
        $invalidItemWriterRegistry,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        ArchiverInterface $writerArchiver1,
        ArchiverInterface $writerArchiver2,
        ArchiverInterface $archiver1,
        ArchiverInterface $archiver2
    ) {
        $invalidItemWriterRegistry->getWriters()->willReturn([$writerArchiver1, $writerArchiver2]);

        $archiver1->getName()->willReturn('archiver_1');
        $archiver2->getName()->willReturn('archiver_2');

        $this->registerArchiver($archiver1);
        $this->registerArchiver($archiver2);

        $event->getJobExecution()->willReturn($jobExecution);

        $writerArchiver1->supports($jobExecution)->willReturn(true);
        $writerArchiver2->supports($jobExecution)->willReturn(false);
        $archiver1->supports($jobExecution)->willReturn(true);
        $archiver2->supports($jobExecution)->willReturn(false);

        $writerArchiver1->archive($jobExecution)->shouldBeCalled();
        $writerArchiver2->archive($jobExecution)->shouldNotBeCalled();
        $archiver1->archive($jobExecution)->shouldBeCalled();
        $archiver2->archive($jobExecution)->shouldNotBeCalled();

        $this->beforeStatusUpgrade($event);
    }
}
