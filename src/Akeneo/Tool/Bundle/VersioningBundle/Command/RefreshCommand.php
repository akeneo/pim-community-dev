<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh versioning data by launching the corresponding batch job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshCommand extends Command
{
    protected static $defaultName = 'pim:versioning:refresh';
    protected static $defaultDescription = 'Version any updated entities';

    private const JOB_CODE = 'versioning_refresh';

    public function __construct(
        private ExecuteJobExecutionHandlerInterface $jobExecutionRunner,
        private CreateJobExecutionHandlerInterface $jobExecutionFactory,
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
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'flush new versions by using this batch size',
                100
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = [
            'batch_size' => (int)$input->getOption('batch-size'),
        ];

        $jobExecution = $this->jobExecutionFactory->createFromBatchCode(self::JOB_CODE, $config, null);
        $jobExecution = $this->jobExecutionRunner->executeFromJobExecutionId($jobExecution->getId());

        if (
            ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode() ||
            (
                ExitStatus::STOPPED === $jobExecution->getExitStatus()->getExitCode() &&
                BatchStatus::STOPPED === $jobExecution->getStatus()->getValue()
            )
        ) {
            $output->writeln(sprintf('<info>Command %s was succesfully executed.</info>', self::$defaultName));

            return Command::SUCCESS;
        }

        $output->writeln(
            sprintf(
                '<error>An error occurred during the %s execution.</error>',
                $jobExecution->getJobInstance()->getType()
            )
        );
        $this->writeExceptions($output, $jobExecution->getFailureExceptions());
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            $this->writeExceptions($output, $stepExecution->getFailureExceptions());
        }

        return Command::FAILURE;
    }

    private function writeExceptions(OutputInterface $output, array $exceptions)
    {
        foreach ($exceptions as $exception) {
            $output->write(
                sprintf(
                    '<error>Error #%s in class %s: %s</error>',
                    $exception['code'],
                    $exception['class'],
                    strtr($exception['message'], $exception['messageParameters'])
                ),
                true
            );
        }
    }
}
