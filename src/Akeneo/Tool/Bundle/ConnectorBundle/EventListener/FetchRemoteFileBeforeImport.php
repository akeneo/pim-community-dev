<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Platform\Job\Infrastructure\Query\GetJobInstanceServerCredentials;
use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceServerCredentials;
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
        private GetJobInstanceServerCredentials $getJobInstanceServerCredentials
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

        $fileSystem = $this->getFileSystem($jobFileLocation, $jobParameters, $jobExecution);
        $remoteStream =  $fileSystem->readStream($jobFileLocation->path());

        file_put_contents($localFilePath, $remoteStream);
        fclose($remoteStream);

        $jobParameters->set('filePath', $localFilePath);
    }

    private function getFileSystem($jobFileLocation, $jobParameters, JobExecution $jobExecution): ?Filesystem
    {
        if (true === $jobFileLocation->isRemote()) {
            return $this->filesystem;
        }

        if ('ftp' === $jobParameters->get('fileSource')) {
            $serverCredentials = $this->getJobInstanceServerCredentials->byJobInstanceCode($jobExecution->getJobInstance()->getCode());
            return $this->getFtpFileSystem($serverCredentials);
        }

        return null;
    }

    private function getFtpFileSystem(JobInstanceServerCredentials $serverCredentials): Filesystem
    {
        // The internal adapter
        $adapter = new League\Flysystem\Ftp\FtpAdapter(
        // Connection options
            League\Flysystem\Ftp\FtpConnectionOptions::fromArray([
                'host' => 'hostname', // required
                'root' => '/root/path/', // required
                'username' => 'username', // required
                'password' => 'password', // required
                'port' => 21,
                'ssl' => false,
                'timeout' => 90,
                'utf8' => false,
                'passive' => true,
                'transferMode' => FTP_BINARY,
                'systemType' => null, // 'windows' or 'unix'
                'ignorePassiveAddress' => null, // true or false
                'timestampsOnUnixListingsEnabled' => false, // true or false
                'recurseManually' => true // true
            ])
        );

        return new Filesystem($adapter);
    }
}
