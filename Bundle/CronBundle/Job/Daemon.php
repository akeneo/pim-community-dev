<?php

namespace Oro\Bundle\CronBundle\Job;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class Daemon
{
    /**
     * Kernel root dir
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Maximum number of concurrent jobs
     *
     * @var int
     */
    protected $maxJobs;

    /**
     * Current environment
     *
     * @var string
     */
    protected $env;

    /**
     * Path to php executable
     *
     * @var string
     */
    protected $phpExec;

    /**
     *
     * @param string $rootDir
     * @param int    $maxJobs [optional] Maximum number of concurent jobs. Default value is 5.
     * @param string $env     [optional] Environment. Default value is "prod".
     */
    public function __construct($rootDir, $maxJobs = 5, $env = 'prod')
    {
        $this->rootDir = $rootDir;
        $this->maxJobs = (int) $maxJobs;
        $this->env     = $env;
    }

    /**
     * Run daemon in background
     *
     * @throws \RuntimeException
     * @return int|null          The process id if running successfully, null otherwise
     */
    public function run()
    {
        if ($this->getPid()) {
            throw new \RuntimeException('Daemon process already started');
        }

        // workaround for Windows
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $wsh = new \COM('WScript.shell');

            $wsh->Run($this->getQueueRunCmd(), 0, false);

            return $this->getPid();
        }

        $process = $this->getQueueRunProcess();

        $process->run();

        return $this->getPid();
    }

    /**
     * Stop daemon
     *
     * @throws \RuntimeException
     * @return bool              True if success, false otherwise
     */
    public function stop()
    {
        $pid = $this->getPid();

        if (!$pid) {
            throw new \RuntimeException('Daemon process not found');
        }

        $process = $this->getQueueStopProcess($pid);

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
        // workaround for Windows
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $output = shell_exec('WMIC path win32_process get Processid,Commandline');

            return preg_match('#console jms-job-queue:run.+(\d+)\s*$#Usm', $output, $matches)
                ? (int) $matches[1]
                : null;
        }

        $process = $this->getPidProcess();

        $process->run();

        return preg_match('#^.+console jms-job-queue:run#Usm', $process->getOutput(), $matches)
            ? (int) $matches[0]
            : null;
    }

    /**
     * Instantiate "ps" *nix command to catch running job queue
     *
     * @return Process
     */
    protected function getPidProcess()
    {
        return new Process('ps ax | grep "[j]ms-job-queue:run"');
    }

    /**
     * Instantiate "ps" *nix command to catch running job queue
     *
     * @return Process
     */
    protected function getQueueRunProcess()
    {
        return new Process($this->getQueueRunCmd());
    }

    /**
     * Instantiate "kill" (*nix) / "taskkill" (Windows) command to terminate job queue
     *
     * @param  int     $pid Process id to kill
     * @return Process
     */
    protected function getQueueStopProcess($pid)
    {
        $cmd = defined('PHP_WINDOWS_VERSION_BUILD') ? 'taskkill /F /PID %u' : 'kill -9 %u';

        return new Process(sprintf($cmd, $pid));
    }

    /**
     * Get command line to run job queue
     *
     * @return string
     */
    protected function getQueueRunCmd()
    {
        if (!$this->phpExec) {
            $finder = new PhpExecutableFinder();

            $this->phpExec = escapeshellarg($finder->find());
        }

        $runCommand = sprintf(
            '%s %sconsole jms-job-queue:run --max-runtime=999999999 --max-concurrent-jobs=%u --env=%s',
            $this->phpExec,
            $this->rootDir . DIRECTORY_SEPARATOR,
            max($this->maxJobs, 1),
            escapeshellarg($this->env)
        );
        
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $runCommand = "nohup {$runCommand} > /dev/null 2>&1 &";
        }

        return $runCommand;
    }
}
