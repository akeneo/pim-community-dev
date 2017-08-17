<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Component\Batch\Event\BatchCommandEvent;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobExecutionMessage;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Queue\JobExecutionQueueInterface;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\StreamHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchCommand extends ContainerAwareCommand
{
    const EXIT_SUCCESS_CODE = 0;
    const EXIT_ERROR_CODE = 1;
    const EXIT_WARNING_CODE = 2;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:batch:job')
            ->setDescription('Launch a registered job instance')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('execution', InputArgument::OPTIONAL, 'Job execution id')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php bin/console akeneo:batch:job -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution',
                null
            )
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                'Don\'t display logs'
            )
            ->addOption(
                'no-lock',
                null,
                InputOption::VALUE_NONE,
                'Don\'t lock command (if command is lockable)'
            )
            ->addOption(
                'synchronous',
                null,
                InputOption::VALUE_NONE,
                'Run the job synchronously, without publishing it to the queue.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $jobInstanceClass = $this->getContainer()->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this->getJobManager()->getRepository($jobInstanceClass)->findOneByCode($code);

        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->getJobRegistry()->get($jobInstance->getJobName());
        $executionId = $input->getArgument('execution');

        if (null !== $executionId && null !== $input->getOption('config')) {
            throw new \InvalidArgumentException('Configuration option cannot be specified when launching a job execution.');
        }

        if (null === $executionId) {
            $jobParameters = $this->createJobParameters($jobInstance, $input);
            $this->validateJobParameters($jobInstance, $jobParameters, $code);
            $jobExecution = $job->getJobRepository()->createJobExecution($jobInstance, $jobParameters);

            $executionId = $jobExecution->getId();

            $options = $this->getProvidedOptions($input->getOptions());
            unset($options['config']);

            $jobExecutionMessage = new JobExecutionMessage($jobExecution, $this->getName(), $options);

            if (!true === $input->getOption('synchronous')) {
                $this->getJobExecutionQueue()->publish($jobExecutionMessage);

                return;
            }
        }

        $event = new BatchCommandEvent($this, $input, $output, $jobInstance);
        $this->getContainer()->get('event_dispatcher')->dispatch(EventInterface::BATCH_COMMAND_START, $event);
        if (false === $event->commandShouldRun()) {
            return self::EXIT_ERROR_CODE;
        }

        $jobExecution = $this->getJobManager()->getRepository(JobExecution::class)->find($executionId);
        if (!$jobExecution) {
            throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
        }
        if (!$jobExecution->getStatus()->isStarting()) {
            throw new \RuntimeException(
                sprintf('Job execution "%s" has invalid status: %s', $executionId, $jobExecution->getStatus())
            );
        }
        if (null === $jobExecution->getExecutionContext()) {
            $jobExecution->setExecutionContext(new ExecutionContext());
        }

        $email = $input->getOption('email');
        if (null !== $email) {
            $this->getMailNotifier()->setRecipientEmail($email);
        }

        if (false === $input->getOption('no-log')) {
            $logger = $this->getContainer()->get('monolog.logger.batch');
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $this
            ->getContainer()
            ->get('akeneo_batch.logger.batch_log_handler')
            ->setSubDirectory($jobExecution->getId());

        $job->execute($jobExecution);

        $job->getJobRepository()->updateJobExecution($jobExecution);

        $verbose = $input->getOption('verbose');
        $exitCode = null;
        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            $nbWarnings = 0;
            /** @var StepExecution $stepExecution */
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $nbWarnings += count($stepExecution->getWarnings());
                if ($verbose) {
                    foreach ($stepExecution->getWarnings() as $warning) {
                        $output->writeln(sprintf('<comment>%s</comment>', $warning->getReason()));
                    }
                }
            }

            if (0 === $nbWarnings) {
                $output->writeln(
                    sprintf(
                        '<info>%s %s has been successfully executed.</info>',
                        ucfirst($jobInstance->getType()),
                        $jobInstance->getCode()
                    )
                );

                $exitCode = self::EXIT_SUCCESS_CODE;
            } else {
                $output->writeln(
                    sprintf(
                        '<comment>%s %s has been executed with %d warnings.</comment>',
                        ucfirst($jobInstance->getType()),
                        $jobInstance->getCode(),
                        $nbWarnings
                    )
                );

                $exitCode = self::EXIT_WARNING_CODE;
            }
        } else {
            $output->writeln(
                sprintf(
                    '<error>An error occurred during the %s execution.</error>',
                    $jobInstance->getType()
                )
            );
            $this->writeExceptions($output, $jobExecution->getFailureExceptions(), $verbose);
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $this->writeExceptions($output, $stepExecution->getFailureExceptions(), $verbose);
            }

            $exitCode = self::EXIT_ERROR_CODE;
        }

        $this->getContainer()->get('event_dispatcher')->dispatch(EventInterface::BATCH_COMMAND_TERMINATE, $event);

        return $exitCode;
    }

    /**
     * Writes failure exceptions to the output
     *
     * @param OutputInterface $output
     * @param array[]         $exceptions
     * @param boolean         $verbose
     */
    protected function writeExceptions(OutputInterface $output, array $exceptions, $verbose) : void
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
            if ($verbose) {
                $output->write(sprintf('<error>%s</error>', $exception['trace']), true);
            }
        }
    }

    /**
     * @return EntityManager
     */
    protected function getJobManager() : EntityManager
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }

    /**
     * @return EntityManager
     */
    protected function getDefaultEntityManager() : EntityManager
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator() : ValidatorInterface
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return Notifier
     */
    protected function getMailNotifier() : Notifier
    {
        return $this->getContainer()->get('akeneo_batch.mail_notifier');
    }

    /**
     * @return JobRegistry
     */
    protected function getJobRegistry() : JobRegistry
    {
        return $this->getContainer()->get('akeneo_batch.job.job_registry');
    }

    /**
     * @return JobParametersFactory
     */
    protected function getJobParametersFactory() : JobParametersFactory
    {
        return $this->getContainer()->get('akeneo_batch.job_parameters_factory');
    }

    /**
     * @return JobParametersValidator
     */
    protected function getJobParametersValidator() : JobParametersValidator
    {
        return $this->getContainer()->get('akeneo_batch.job.job_parameters_validator');
    }

    /**
     * @return JobExecutionQueueInterface
     */
    protected function getJobExecutionQueue() : JobExecutionQueueInterface
    {
        return $this->getContainer()->get('akeneo_batch.queue.database_job_execution_queue');
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return string
     */
    private function getErrorMessages(ConstraintViolationList $errors) : string
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }

    /**
     * @param string $data
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function decodeConfiguration($data) : array
    {
        $config = json_decode($data, true);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return $config;
        }

        throw new \InvalidArgumentException($error);
    }

    /**
     * @param JobInstance    $jobInstance
     * @param InputInterface $input
     *
     * @return JobParameters
     */
    private function createJobParameters(JobInstance $jobInstance, InputInterface $input) : JobParameters
    {
        $job = $this->getJobRegistry()->get($jobInstance->getJobName());
        $jobParamsFactory = $this->getJobParametersFactory();
        $rawParameters = $jobInstance->getRawParameters();

        $config = $input->getOption('config') ? $this->decodeConfiguration($input->getOption('config')) : [];

        $rawParameters = array_merge($rawParameters, $config);
        $jobParameters = $jobParamsFactory->create($job, $rawParameters);

        return $jobParameters;
    }

    /**
     * @param JobInstance   $jobInstance
     * @param JobParameters $jobParameters
     * @param string        $code
     *
     * @throws \RuntimeException
     */
    private function validateJobParameters(JobInstance $jobInstance, JobParameters $jobParameters, string $code) : void
    {
        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $defaultJobInstance = $this->getDefaultEntityManager()->merge($jobInstance);
        $job = $this->getJobRegistry()->get($jobInstance->getJobName());
        $paramsValidator = $this->getJobParametersValidator();
        $errors = $paramsValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (count($errors) > 0) {
            throw new \RuntimeException(
                sprintf(
                    'Job instance "%s" running the job "%s" with parameters "%s" is invalid because of "%s"',
                    $code,
                    $job->getName(),
                    print_r($jobParameters->all(), true),
                    $this->getErrorMessages($errors)
                )
            );
        }

        $this->getDefaultEntityManager()->clear(get_class($jobInstance));
    }

    /**
     * Get the options provided by the user in the command line.
     *
     * @param $options
     *
     * @return array
     */
    private function getProvidedOptions(array $options) : array
    {
        $providedOptions = array_filter($options, function($optionValue) {
            return null !== $optionValue && false !== $optionValue;
        });

        return $providedOptions;
    }
}
