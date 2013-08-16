<?php

namespace Oro\Bundle\CronBundle\Job;

use Symfony\Component\Process\Process;

class Daemon
{
    /**
     * Kernel root dir
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Cache dir
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Maximum number of concurrent jobs
     *
     * @var int
     */
    protected $maxJobs;

    /**
     *
     * @param string $rootDir
     * @param string $cacheDir
     * @param string $maxJobs
     */
    public function __construct($rootDir, $cacheDir, $maxJobs = 5)
    {
        $this->rootDir  = $rootDir;
        $this->cacheDir = $cacheDir;
        $this->maxJobs  = (int) $maxJobs;
    }

    /**
     * Run daemon
     *
     * @throws \RuntimeException
     * @return bool|int Process id if started successfully, false otherwise
     */
    public function run()
    {
        if ($this->getPid()) {
            throw false;
        }

        $process = new Process(
            sprintf(
                'php %sconsole jms-job-queue:run --max-runtime=999999999 --max-concurrent-jobs=%u',
                $this->rootDir . DIRECTORY_SEPARATOR,
                max($this->maxJobs, 1)
            )
        );

        $process->start();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getPid();
    }

    /**
     * Stop daemon
     *
     * @throws \RuntimeException
     * @return bool True if success, false otherwise
     */
    public function stop()
    {
        $pid = $this->getPid();

        if (!$pid) {
            throw new \RuntimeException('Daemon process not found');
        }

        $process = new Process(sprintf('kill -9 %u', $pid));

        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Check if jobs queue daemon is running
     *
     * @return int|null Daemon process id on success, null otherwise
     */
    public function getPid()
    {
        $cmd = defined('PHP_WINDOWS_VERSION_BUILD')
            ? 'WMIC path win32_process get Processid,Commandline'
            : 'ps ax | grep "[j]ms-job-queue:run"';

        $process = new Process($cmd);

        $process->run();

        return preg_match('#^.+console jms-job-queue:run#Usm', $process->getOutput(), $matches)
            ? (int) $matches[0]
            : null;
    }
}
