<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Command;

use Akeneo\Platform\Bundle\ImportExportBundle\Purge\PurgeJobExecution;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Purge Jobs Execution history.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeJobExecutionCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:purge-job-execution';
    protected static $defaultDescription = 'Purge jobs execution older than number of days you want except the last one.
             If the value is equals to 0, it will delete everything. By default 90 days, minimum is 0 day';

    private const DEFAULT_NUMBER_OF_DAYS = 90;

    public function __construct(
        private PurgeJobExecution $purgeJobExecution,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'job_instance',
                'j',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filter(s) on job instance to purge, if none explicitly passed, all job executions will be purged',
                []
            )
            ->addOption(
                'status',
                's',
                InputOption::VALUE_OPTIONAL,
                'Filter on status to purge, if none explicitly passed, all job executions will be purged',
            )
            ->addOption(
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = $this->getDays($input, $output);
        if (false === $days) {
            return Command::FAILURE;
        }

        $jobInstances = $input->getOption('job_instance');
        $status = $this->getStatus($input);

        if (0 === $days) {
            $numberOfDeletedJobExecutions = $this->purgeJobExecution->all($jobInstances, $status);
        } else {
            $numberOfDeletedJobExecutions = $this->purgeJobExecution->olderThanDays($days, $jobInstances, $status);
        }

        $output->write('All jobs execution');
        if (0 !== $days) {
            $output->write(sprintf(' older than %d days', $days));
        }

        if (null !== $status) {
            $output->write(sprintf(' with status %s', $status->__toString()));
        }

        if (!empty($jobInstances)) {
            $output->write(sprintf(" with job instance code '%s'", implode(', ', $jobInstances)));
        }

        $output->writeln(' have been purged');
        $output->writeln(sprintf('%d jobs execution deleted', $numberOfDeletedJobExecutions));

        return Command::SUCCESS;
    }

    private function getDays(InputInterface $input, OutputInterface $output): int|false
    {
        $days = $input->getOption('days');
        if (!is_numeric($days)) {
            $output->writeln(
                sprintf('<error>Option --days must be a number, "%s" given.</error>', $input->getOption('days'))
            );

            return false;
        }

        $days = (int) $days;
        if (0 === $days) {
            $confirmation = new ConfirmationQuestion('This will delete ALL job executions. Do you confirm? ', false);
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            if (!$questionHelper->ask($input, $output, $confirmation)) {
                $output->writeln('Operation aborted');

                return false;
            }
        }

        return $days;
    }

    private function getStatus(InputInterface $input): ?BatchStatus
    {
        if (null !== $input->getOption('status')) {
            return new BatchStatus((int) $input->getOption('status'));
        }

        return null;
    }
}
