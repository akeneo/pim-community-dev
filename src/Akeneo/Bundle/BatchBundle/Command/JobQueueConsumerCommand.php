<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Component\Batch\Job\JobExecutionQueueRepositoryInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\System\SystemIdProvider;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * This command is a daemon to consume job execution messages and launch the associated job execution in background.
 * A command can only execute one single job at a time.
 * The command will not launch any other jobs until the current job is not finished.
 *
 * If you want to execute several jobs in parallel, you have to run this command several times, according to your needs.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobQueueConsumerCommand extends ContainerAwareCommand
{
    /** Prefix to add when locking a job execution message in database. */
    const LOCK_PREFIX = 'akeneo_job_execution_';

    /** Interval in seconds before checking if a new message is in the queue. */
    const QUEUE_CHECK_INTERVAL = 5;

    /** Interval in seconds before checking if the job is still running. */
    const HEALTH_CHECK_INTERVAL = 5;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:batch:job-consumer')
            ->setDescription('Launch a registered job instance')
            ->addArgument('consumer_name', InputArgument::REQUIRED, 'Consumer name')
            ->addOption('run-once', null, InputOption::VALUE_NONE, 'Launch only one job execution.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $systemId = $this->getSystemIdProvider()->getSystemId();
        $consumer = sprintf('%s_%s', $systemId, $input->getArgument('consumer_name'));

        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s/bin/console', $this->getProjectDir());

        $processBuilder = ProcessBuilder::create();
        $processBuilder
            ->setPrefix([$pathFinder->find(), $console])
            ->setTimeout(null);

        do {
            $jobExecutionMessage = $this->getQueueRepository()->getLastJobExecutionMessage();
            if (null === $jobExecutionMessage) {
                continue;
            }

            $lock = self::LOCK_PREFIX . $jobExecutionMessage['job_execution_id'];
            if (!$this->getLock($lock)) {
                continue;
            }

            $processBuilder->setArguments($this->getArguments($jobExecutionMessage));
            $process = $processBuilder->getProcess();

            $process->start();

            $this->getQueueRepository()->updateConsumerName($jobExecutionMessage['id'], $consumer);
            $this->releaseLock($lock);

            do {
                $this->getJobRepository()->updateHealthCheck($jobExecutionMessage['job_execution_id']);
                sleep(self::HEALTH_CHECK_INTERVAL);
            } while ($process->isRunning());

        } while (false === $input->getOption('run-once') && 0 === sleep(self::QUEUE_CHECK_INTERVAL));
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     *
     * @param array $jobExecutionMessage
     *
     * @return array
     */
    private function getArguments(array $jobExecutionMessage) : array
    {
        $arguments = [
            $jobExecutionMessage['command_name'],
            $jobExecutionMessage['job_instance_code'],
            $jobExecutionMessage['job_execution_id'],
        ];

        $options = json_decode($jobExecutionMessage['options'], true);
        foreach ($options as $optionsName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionValue);
            }
            if (false !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionsName, $optionValue);
            }
        }

        return $arguments;
    }

    /**
     * Try to get an application-level lock.
     *
     * @param string $lock
     *
     * @return bool return true if lock has been acquired, false otherwise
     */
    private function getLock(string $lock) : bool
    {
        $connection = $this->getDbalConnection();
        $stmt = $connection->prepare('SELECT GET_LOCK(:lock, 0)');
        $stmt->bindParam('lock', $lock);

        $stmt->execute();
        $result = $stmt->fetch();

        return '1' === current($result);
    }

    /**
     * Release an application-level lock.
     *
     * @param string $lock
     */
    private function releaseLock(string $lock) : void
    {
        $connection = $this->getDbalConnection();
        $stmt = $connection->prepare('SELECT RELEASE_LOCK(:lock)');
        $stmt->bindParam('lock', $lock);

        $stmt->execute();
    }

    /**
     * @return SystemIdProvider
     */
    private function getSystemIdProvider() : SystemIdProvider
    {
        return $this->getContainer()->get('akeneo_batch.system.mac_address_provider');
    }

    /**
     * @return JobExecutionQueueRepositoryInterface
     */
    private function getQueueRepository() : JobExecutionQueueRepositoryInterface
    {
        return $this->getContainer()->get('akeneo_batch.job.job_execution_queue_repository');
    }

    /**
     * @return JobRepositoryInterface
     */
    private function getJobRepository() : JobRepositoryInterface
    {
        return $this->getContainer()->get('akeneo_batch.job_repository');
    }

    /**
     * @return Connection
     */
    private function getDbalConnection() : Connection
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getConnection();
    }


    /**
     * @return string
     */
    private function getProjectDir() : string
    {
        return $this->getContainer()->getParameter('kernel.project_dir');
    }
}
