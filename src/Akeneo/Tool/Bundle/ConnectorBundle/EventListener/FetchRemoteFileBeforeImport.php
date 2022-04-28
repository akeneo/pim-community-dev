<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\Job\Infrastructure\Flysystem\Sftp\SftpAdapterFactory;
use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\GetJobInstanceRemoteStorage;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemReader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * As the import system only work on local file, we need to fetch the file to import
 * from a Flysystem storage if it's on one of them.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchRemoteFileBeforeImport implements EventSubscriberInterface
{
    public function __construct(
        private FilesystemReader $filesystem,
        private GetJobInstanceRemoteStorage $getJobInstanceRemoteStorage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'fetchRemoteFile'
        ];
    }

    /**
     * Fetch a remote file if needed in case of import
     */
    public function fetchRemoteFile(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $jobParameters = $jobExecution->getJobParameters();

        if (null === $jobParameters ||
            !$jobParameters->has('filePath') ||
            JobInstance::TYPE_IMPORT !== $jobExecution->getJobInstance()->getType()) {
            return;
        }

        $jobFileLocation = JobFileLocation::parseUrl($jobParameters->get('filePath'));
        $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
        $localFilePath = $workingDirectory.DIRECTORY_SEPARATOR.basename($jobFileLocation->path());

        $fileSystem = $this->getFileSystem($jobFileLocation, $jobExecution);
        $remoteStream = $fileSystem->readStream($jobFileLocation->path());

        file_put_contents($localFilePath, $remoteStream);
        fclose($remoteStream);

        $jobParameters->set('filePath', $localFilePath);
    }

    private function getFileSystem(JobFileLocation $jobFileLocation, JobExecution $jobExecution): ?FilesystemReader
    {
        if (true === $jobFileLocation->isRemote()) {
            return $this->filesystem;
        }

        $jobInstanceRemoteStorage = $this->getJobInstanceRemoteStorage->byJobInstanceCode($jobExecution->getJobInstance()->getCode());
        $sftpAdapter = SftpAdapterFactory::fromJobInstanceRemoteStorage($jobInstanceRemoteStorage);

        return new Filesystem($sftpAdapter);
    }
}
