<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;

class JobExecutionArchivistSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionArchivist::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
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
        JobExecution $jobExecution,
        ArchiverInterface $archiver,
        ArchiverInterface $archiver2
    ) {
        $jobExecution->isRunning()->willReturn(false);

        $archiver->getName()->willReturn('output');
        $archiver->getArchives($jobExecution)->willReturn(['a', 'b']);
        $this->registerArchiver($archiver);

        $archiver2->getName()->willReturn('input');
        $archiver2->getArchives($jobExecution)->willReturn(['a', 'b']);
        $this->registerArchiver($archiver2);

        $this->getArchives($jobExecution)->shouldReturn(['output' => ['a', 'b'], 'input' => ['a', 'b']]);
    }

    function it_throws_an_exception_if_no_archiver_is_defined(
        JobExecution $jobExecution,
        ArchiverInterface $archiver
    ) {
        $archiver->getName()->willReturn('archiver');

        $this->registerArchiver($archiver);

        $this
            ->shouldThrow('\InvalidArgumentException')
            ->during('getArchive', [$jobExecution, 'archiver_name', 'key']);
    }

    function it_returns_the_corresponding_archiver(JobExecution $jobExecution, ArchiverInterface $archiver)
    {
        $archiver->getName()->willReturn('output');
        $archiver->getArchive($jobExecution, 'key')->shouldBeCalled();
        $this->registerArchiver($archiver);

        $this->getArchive($jobExecution, 'output', 'key');
    }

    function it_register_an_event_and_verify_if_job_is_supported(
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        ArchiverInterface $archiver1,
        ArchiverInterface $archiver2
    ) {
        $archiver1->getName()->willReturn('archiver_1');
        $archiver2->getName()->willReturn('archiver_2');

        $this->registerArchiver($archiver1);
        $this->registerArchiver($archiver2);

        $event->getJobExecution()->willReturn($jobExecution);

        $archiver1->supports($jobExecution)->willReturn(true);
        $archiver2->supports($jobExecution)->willReturn(false);

        $archiver1->archive($jobExecution)->shouldBeCalled();
        $archiver2->archive($jobExecution)->shouldNotBeCalled();

        $this->beforeStatusUpgrade($event);
    }
}
