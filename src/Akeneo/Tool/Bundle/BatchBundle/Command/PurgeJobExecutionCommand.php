<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
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
            'How many days of jobs execution you want to keep',
            self::DEFAULT_NUMBER_OF_DAYS
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getOption('days');
        if (!is_numeric($days)) {
            $output->writeln(
                sprintf('<error>Option --days must be a number, "%s" given.</error>', $input->getOption('days'))
            );

            return;
        }

        $deleteJobExecution = $this->getDeleteJobExecutionQuery();
        $numberOfDeletedJobExecution = $deleteJobExecution->olderThanDays($days);

        $output->write(sprintf("%s jobs execution deleted ...\n", $numberOfDeletedJobExecution));
    }

    /**
     * @return DoctrineJobRepository
     */
    protected function getDeleteJobExecutionQuery(): DeleteJobExecution
    {
        return $this->getContainer()->get('akeneo_batch.delete_job_execution');
    }
}
