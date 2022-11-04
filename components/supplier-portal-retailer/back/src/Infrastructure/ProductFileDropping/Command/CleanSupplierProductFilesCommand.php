<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Command;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CleanSupplierProductFilesCommand extends Command
{
    protected static $defaultName = 'akeneo:supplier-portal:clean-supplier-product-files';
    protected static $defaultDescription = 'Clean old supplier product files';
    private const JOB_CODE = 'supplier_portal_supplier_product_files_clean';

    public function __construct(
        private ExecuteJobExecutionHandlerInterface $jobExecutionRunner,
        private CreateJobExecutionHandlerInterface $jobExecutionFactory,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobExecution = $this->jobExecutionFactory->createFromBatchCode(self::JOB_CODE, [], null);
        $jobExecution = $this->jobExecutionRunner->executeFromJobExecutionId($jobExecution->getId());

        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            $output->writeln(sprintf('<info>Command %s was succesfully executed.</info>', self::$defaultName));

            return Command::SUCCESS;
        }

        $output->writeln(
            sprintf(
                '<error>An error occurred during the execution of the "%s" job.</error>',
                $jobExecution->getJobInstance()->getCode(),
            ),
        );
        $this->writeExceptions($output, $jobExecution->getFailureExceptions());
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            $this->writeExceptions($output, $stepExecution->getFailureExceptions());
        }

        return Command::FAILURE;
    }

    private function writeExceptions(OutputInterface $output, array $exceptions): void
    {
        foreach ($exceptions as $exception) {
            $output->writeln(
                sprintf(
                    '<error>Error #%s in class %s: %s</error>',
                    $exception['code'],
                    $exception['class'],
                    strtr($exception['message'], $exception['messageParameters']),
                ),
            );
        }
    }
}
