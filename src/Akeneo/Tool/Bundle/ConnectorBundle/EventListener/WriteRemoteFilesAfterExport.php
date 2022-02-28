<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\Job\Infrastructure\Query\GetJobInstanceServerCredentials;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Ftp\FtpConnectionProvider;
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
        private JobRegistry $jobRegistry,
        private FilesystemProvider $filesystemProvider,
        private GetJobInstanceServerCredentials $getJobInstanceServerCredentials,
        private LoggerInterface $logger
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
        $serverCredentials = $this->getJobInstanceServerCredentials->byJobInstanceCode($jobExecution->getJobInstance()->getCode());
        if ($serverCredentials === null) {
            return;
        }

        $normalizedServerCredentials = $serverCredentials->normalize();
        $destinationOptions = FtpConnectionOptions::fromArray([
            'host' => $normalizedServerCredentials['host'],
            'root' => '',
            'username' => $normalizedServerCredentials['user'],
            'password' => $normalizedServerCredentials['password'],
            'port' => $normalizedServerCredentials['port'],
        ]);

        $ftpFilesystem = new Filesystem(new FtpAdapter($destinationOptions));
        $connectionProvider = new FtpConnectionProvider();
        $connectionProvider->createConnection($destinationOptions);

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
                    $localAdepter = new LocalFilesystemAdapter('/');
                    $sourceFilesystem = new Filesystem($localAdepter);
                } else {
                    $sourceFilesystem = $this->filesystemProvider->getFilesystem($writtenFile->sourceStorage());
                }

                $outputFilePath = \sprintf(
                    '%s%s%s',
                    $outputDirectory,
                    DIRECTORY_SEPARATOR,
                    $writtenFile->outputFilepath()
                );
                try {
                    $this->transferFile($sourceFilesystem, $ftpFilesystem, $writtenFile->sourceKey(), $outputFilePath);
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
