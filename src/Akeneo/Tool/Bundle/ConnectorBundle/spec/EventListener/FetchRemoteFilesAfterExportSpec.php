<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\VersionProviderInterface;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\FetchRemoteFilesAfterExport;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FetchRemoteFilesAfterExportSpec extends ObjectBehavior
{
    function let(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($jobRegistry, $versionProvider, $filesystemProvider, $fileFetcher, $logger);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FetchRemoteFilesAfterExport::class);
    }

    function it_subscribes_to_before_job_status_upgrade_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(JobExecutionEvent::BEFORE_JOB_STATUS_UPGRADE);
    }

    function it_does_nothing_for_saas_editions(
        VersionProviderInterface $versionProvider,
        FileFetcherInterface $fileFetcher,
        JobExecutionEvent $event
    ) {
        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(true);
        $fileFetcher->fetch(Argument::cetera())->shouldNotBeCalled();

        $this->fetchRemoteFiles($event);
    }

    function it_does_nothing_when_steps_are_not_item_steps(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FileFetcherInterface $fileFetcher,
        Job $job,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        StepInterface $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getJobName()->willReturn('job_name');
        $job->getSteps()->willReturn([$step]);

        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);
        $jobRegistry->get('job_name')->shouldBeCalled()->willReturn($job);
        $fileFetcher->fetch(Argument::cetera())->shouldNotBeCalled();

        $this->fetchRemoteFiles(new JobExecutionEvent($jobExecution->getWrappedObject()));
    }

    function it_does_nothing_for_a_non_archivable_writer(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FileFetcherInterface $fileFetcher,
        Job $job,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        ItemStep $step,
        ItemWriterInterface $writer
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getJobName()->willReturn('job_name');
        $step->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step]);

        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);
        $jobRegistry->get('job_name')->shouldBeCalled()->willReturn($job);
        $fileFetcher->fetch(Argument::cetera())->shouldNotBeCalled();

        $this->fetchRemoteFiles(new JobExecutionEvent($jobExecution->getWrappedObject()));
    }

    function it_fetches_remote_files_into_the_export_directory(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        Job $job,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        ItemStep $step,
        FilesystemInterface $catalogFilesystem,
        FilesystemInterface $assetFilesystem
    ) {
        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array
            {
                return [
                    WrittenFileInfo::fromFileStorage('a/b/image.jpg', 'catalogStorage', 'files/sku1/picture/image.jpg'),
                    WrittenFileInfo::fromFileStorage('d/e/notice.pdf', 'assetStorage', 'files/sku2/notice/notice.pdf'),
                ];
            }
            public function write(array $items): void
            {
            }
            public function getPath(): string
            {
                return '/my/export/path/Export_job_name.xlsx';
            }
        };

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getJobName()->willReturn('job_name');
        $step->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step]);

        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);
        $jobRegistry->get('job_name')->shouldBeCalled()->willReturn($job);

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogFilesystem);
        $fileFetcher->fetch(
            $catalogFilesystem,
            'a/b/image.jpg',
            ['filePath' => '/my/export/path/files/sku1/picture', 'filename' => 'image.jpg']
        )->shouldBeCalled();

        $filesystemProvider->getFilesystem('assetStorage')->shouldBeCalled()->willReturn($assetFilesystem);
        $fileFetcher->fetch(
            $assetFilesystem,
            'd/e/notice.pdf',
            ['filePath' => '/my/export/path/files/sku2/notice', 'filename' => 'notice.pdf']
        )->shouldBeCalled();

        $this->fetchRemoteFiles(new JobExecutionEvent($jobExecution->getWrappedObject()));
    }

    function it_does_not_throw_any_exception_if_the_fetch_is_unsuccessful(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        LoggerInterface $logger,
        Job $job,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        ItemStep $step,
        FilesystemInterface $catalogFilesystem
    ) {
        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array
            {
                return [
                    WrittenFileInfo::fromFileStorage('a/b/image.jpg', 'catalogStorage', 'files/sku1/picture/image.jpg'),
                ];
            }
            public function write(array $items): void
            {
            }
            public function getPath(): string
            {
                return '/my/export/path/Export_job_name.xlsx';
            }
        };

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getJobName()->willReturn('job_name');
        $step->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step]);

        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);
        $jobRegistry->get('job_name')->shouldBeCalled()->willReturn($job);

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogFilesystem);

        $fileFetcher->fetch(
            $catalogFilesystem,
            'a/b/image.jpg',
            ['filePath' => '/my/export/path/files/sku1/picture', 'filename' => 'image.jpg']
        )->shouldBeCalled()->willThrow(new FileTransferException('The file could not be fetched'));
        $logger->warning(
            'The remote file could not be fetched into the local filesystem',
            [
                'key' => 'a/b/image.jpg',
                'storage' => 'catalogStorage',
                'destination' => '/my/export/path/files/sku1/picture/image.jpg',
                'exception' => [
                    'type' => FileTransferException::class,
                    'message' => 'The file could not be fetched',
                ],
            ]
        )->shouldBeCalled();

        $this->shouldNotThrow(\Exception::class)->during(
            'fetchRemoteFiles',
            [
                new JobExecutionEvent(
                    $jobExecution->getWrappedObject()
                ),
            ]
        );
    }
}
