<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use League\Flysystem\Filesystem;

class LogArchiver implements ArchiverInterface
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        $logPath = $jobExecution->getLogFile();

        if (is_file($logPath)) {
            $log = fopen($logPath, 'r');
            $this->filesystem->writeStream($logPath, $log);
            fclose($log);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        return in_array($jobExecution->getJobInstance()->getType(), [JobInstance::TYPE_EXPORT, JobInstance::TYPE_IMPORT]);
    }

    /**
     * {@inheritDoc}
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $archives = [];

        return $archives;
    }

    /**
     * {@inheritDoc}
     */
    public function getArchive(JobExecution $jobExecution, $key)
    {
        // TODO: Implement getArchive() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'log';
    }
}
