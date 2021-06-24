<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
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
    private FilesystemReader $filesystem;

    public function __construct(FilesystemReader $filesystem)
    {
        $this->filesystem = $filesystem;
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

        if (true === $jobFileLocation->isRemote()) {
            $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $localFilePath = $workingDirectory.DIRECTORY_SEPARATOR.basename($jobFileLocation->path());

            $remoteStream =  $this->filesystem->readStream($jobFileLocation->path());

            file_put_contents($localFilePath, $remoteStream);
            fclose($remoteStream);

            $jobParameters->set('filePath', $localFilePath);
        }
    }
}
