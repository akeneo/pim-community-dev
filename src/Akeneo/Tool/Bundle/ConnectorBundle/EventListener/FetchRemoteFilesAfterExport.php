<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\VersionProviderInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This subscriber fetches remote files into the expected export directory after an export
 */
class FetchRemoteFilesAfterExport implements EventSubscriberInterface
{
    private JobRegistry $jobRegistry;
    private VersionProviderInterface $versionProvider;
    private FilesystemProvider $filesystemProvider;
    private FileFetcherInterface $fileFetcher;
    private LoggerInterface $logger;

    public function __construct(
        JobRegistry $jobRegistry,
        VersionProviderInterface $versionProvider,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        LoggerInterface $logger
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->versionProvider = $versionProvider;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'fetchRemoteFiles',
        ];
    }

    public function fetchRemoteFiles(JobExecutionEvent $event): void
    {
        if ($this->versionProvider->isSaaSVersion()) {
            return;
        }

        $jobExecution = $event->getJobExecution();
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }

            $writer = $step->getWriter();
            if (!$writer instanceof ArchivableWriterInterface) {
                continue;
            }

            $outputDirectory = \dirname($writer->getPath());
            foreach ($writer->getWrittenFiles() as $writtenFile) {
                if ($writtenFile->isLocalFile()) {
                    continue;
                }
                $filesystem = $this->filesystemProvider->getFilesystem($writtenFile->sourceStorage());
                $outputFilePath = \sprintf(
                    '%s%s%s',
                    $outputDirectory,
                    DIRECTORY_SEPARATOR,
                    $writtenFile->outputFilepath()
                );
                try {
                    $this->fileFetcher->fetch(
                        $filesystem,
                        $writtenFile->sourceKey(),
                        [
                            'filePath' => \dirname($outputFilePath),
                            'filename' => \basename($outputFilePath),
                        ]
                    );
                } catch (FileTransferException | \LogicException $e) {
                    $this->logger->warning(
                        'The remote file could not be fetched into the local filesystem',
                        [
                            'key' => $writtenFile->sourceKey(),
                            'storage' => $writtenFile->sourceStorage(),
                            'destination' => $outputFilePath,
                            'exception' => [
                                'type' => \get_class($e),
                                'message' => $e->getMessage(),
                            ],
                        ]
                    );

                    continue;
                }
            }
        }
    }
}
