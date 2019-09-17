<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Purge Jobs Execution history
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeJobExecutionCommand extends ContainerAwareCommand
{
    const DEFAULT_NUMBER_OF_DAYS = 90;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('akeneo:batch:purge-job-execution');
        $this->setDescription(
            'Purge jobs execution older than number of days you want except the last one, by default 90 days'
        );
        $this->addOption(
            'days',
            'd',
            InputOption::VALUE_OPTIONAL,
            'How many days of jobs execution you want to keep'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $this->getNumberOfDaysOption($input);

        $numberOfDeletedJobExecution = $this->getDeleteJobExecutionQuery()->olderThanDays($days);
        $output->writeln(sprintf("%s jobs execution deleted ...", $numberOfDeletedJobExecution));

        $this->deleteJobExecutionMessageOrphans();
    }

    /**
     * @param InputInterface $input
     *
     * @return int
     */
    protected function getNumberOfDaysOption(InputInterface $input)
    {
        if ($input->getOption('days') && (int) $input->getOption('days')) {
            return (int) $input->getOption('days');
        }

        return self::DEFAULT_NUMBER_OF_DAYS;
    }

    /**
     * @return DeleteJobExecution
     */
    protected function getDeleteJobExecutionQuery(): DeleteJobExecution
    {
        return $this->getContainer()->get('akeneo_batch.delete_job_execution');
    }

    private function deleteJobExecutionMessageOrphans(): void
    {
        $this->getContainer()->get('akeneo_batch_queue.query.delete_job_execution_message_orphans')->execute();
    }
}
