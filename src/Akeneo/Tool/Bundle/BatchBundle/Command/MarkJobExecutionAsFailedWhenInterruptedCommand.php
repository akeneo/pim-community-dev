<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Akeneo\Tool\Component\Batch\Query\MarkJobExecutionAsFailedWhenInterrupted;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarkJobExecutionAsFailedWhenInterruptedCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:clean-job-executions';
    private MarkJobExecutionAsFailedWhenInterrupted $markJobExecutionAsFailedWhenInterrupted;

    public function __construct(
        MarkJobExecutionAsFailedWhenInterrupted $markJobExecutionAsFailedWhenInterrupted
    ) {
        parent::__construct();
        $this->markJobExecutionAsFailedWhenInterrupted = $markJobExecutionAsFailedWhenInterrupted;
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'jobCodes',
                InputArgument::REQUIRED,
                'Job instance codes that need to have job executions to be cleaned. For example: "job_1,job_2".'
            )
            ->setDescription(
            'When jobs are launched with the if an error happen the job execution crashes and need to be cleaned.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobCodes = $input->getArgument('jobCodes');
        $jobCodes =  array_map('trim', explode(',', trim($jobCodes)));

        $impactedRows = $this->markJobExecutionAsFailedWhenInterrupted->execute($jobCodes);
        $output->writeln(sprintf('<info>%s job executions cleaned</info>', $impactedRows));

        return 0;
    }
}
