<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\EventListener;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;

class JobExecutionArchivistSpec extends ObjectBehavior
{
    function let(InvalidItemsCollector $collector, CsvWriter $writer, Filesystem $filesystem)
    {
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist');
    }

    function it_returns_subscribed_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeStatusUpgrade'
            ]
        );
    }

    function it_throws_an_exception_if_there_is_already__a_registered_archier(ArchiverInterface $archiver)
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

    function it_throws_an_exception_if_there_is_no_registered_archivers(JobExecution $jobExecution)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getArchive', [$jobExecution, 'archiver_name', 'key']);
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
        ArchiverInterface $archiver
    ) {
        $archiver->getName()->willReturn('output');
        $this->registerArchiver($archiver);

        $event->getJobExecution()->willReturn($jobExecution);

        $archiver->supports($jobExecution)->willReturn(true)->shouldBeCalled();
        $archiver->archive($jobExecution)->shouldBeCalled();

        $this->beforeStatusUpgrade($event);
    }
}
