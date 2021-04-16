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
    private array $defaultJobCodes;

    public function __construct(
        MarkJobExecutionAsFailedWhenInterrupted $markJobExecutionAsFailedWhenInterrupted,
        array $defaultJobCodes
    ) {
        parent::__construct();
        $this->markJobExecutionAsFailedWhenInterrupted = $markJobExecutionAsFailedWhenInterrupted;
        $this->defaultJobCodes = $defaultJobCodes;
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'jobCodes',
                InputArgument::OPTIONAL,
                'Job instance codes that need to have job executions to be clean. For example: "job_1,job_2".'
            )
            ->setDescription(
            'When jobs are launched with the if an error happen the job execution crashes and need to be cleaned.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobCodes = $input->getArgument('jobCodes');
        $jobCodes = null !== $jobCodes ?
            array_map('trim', explode(',', trim($jobCodes))) :
            $this->defaultJobCodes;

        $impactedRows = $this->markJobExecutionAsFailedWhenInterrupted->execute($jobCodes);
        $output->writeln(sprintf('<info>%s rows cleaned</info>', $impactedRows));

        return 0;
    }
}
