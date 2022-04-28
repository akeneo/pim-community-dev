<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\Job\Infrastructure\Flysystem\Sftp\SftpAdapterFactory;
use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\GetJobInstanceRemoteStorage;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WriteRemoteFilesAfterExport implements EventSubscriberInterface
{
    public function __construct(
        private JobRegistry                 $jobRegistry,
        private FilesystemProvider          $filesystemProvider,
        private GetJobInstanceRemoteStorage $getJobInstanceRemoteStorage,
        private LoggerInterface             $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'writeRemoteFiles',
        ];
    }

    public function writeRemoteFiles(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        if (JobInstance::TYPE_EXPORT !== $jobExecution->getJobInstance()->getType()) {
            return;
        }

        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        $jobInstanceRemoteStorage = $this->getJobInstanceRemoteStorage->byJobInstanceCode($jobExecution->getJobInstance()->getCode());
        if ($jobInstanceRemoteStorage === null) {
            return;
        }

        $sftpAdapter = SftpAdapterFactory::fromJobInstanceRemoteStorage($jobInstanceRemoteStorage);
        $sftpFilesystem = new Filesystem($sftpAdapter);

        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }

            $writer = $step->getWriter();
            if (!$writer instanceof ArchivableWriterInterface) {
                continue;
            }

            foreach ($writer->getWrittenFiles() as $writtenFile) {
                if ($writtenFile->isLocalFile()) {
                    $localAdepter = new LocalFilesystemAdapter('/');
                    $sourceFilesystem = new Filesystem($localAdepter);
                } else {
                    $sourceFilesystem = $this->filesystemProvider->getFilesystem($writtenFile->sourceStorage());
                }

                try {
                    $this->transferFile($sourceFilesystem, $sftpFilesystem, $writtenFile->sourceKey(), $writtenFile->outputFilepath());
                } catch (FileTransferException | \LogicException $e) {
                    $this->logger->warning(
                        'The remote file could not be fetched into the local filesystem',
                        [
                            'key' => $writtenFile->sourceKey(),
                            'storage' => $writtenFile->sourceStorage(),
                            'destination' => $writtenFile->outputFilepath(),
                            'exception' => [
                                'type' => \get_class($e),
                                'message' => $e->getMessage(),
                            ],
                        ]
                    );
                }
            }
        }
    }

    public function transferFile(Filesystem $sourceFilesystem, Filesystem $destinationFilesystem, string $sourceFilePath, string $destinationFilePath)
    {
        if (!$sourceFilesystem->fileExists($sourceFilePath)) {
            throw new \LogicException(sprintf('The file "%s" is not present on the source filesystem.', $sourceFilePath));
        }

        try {
            $stream = $sourceFilesystem->readStream($sourceFilePath);
        } catch (UnableToReadFile $e) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the source filesystem.', $sourceFilePath),
                0,
                $e
            );
        }

        try {
            $destinationFilesystem->writeStream($destinationFilePath, $stream);
        } catch (UnableToWriteFile $e) {
            throw new FileTransferException(
                sprintf('Unable to write the file "%s" from the destination filesystem.', $destinationFilePath),
                $e->getCode(),
                $e
            );
        }
    }
}
