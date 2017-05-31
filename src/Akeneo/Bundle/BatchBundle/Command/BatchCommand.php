<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\StreamHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator;

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
                'php app/console akeneo:batch:job -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution'
            )
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                'Don\'t display logs'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noLog = $input->getOption('no-log');

        if (!$noLog) {
            $logger = $this->getContainer()->get('monolog.logger.batch');
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $code = $input->getArgument('code');

        $jobInstance = $this->getJobManager()->getRepository(JobInstance::class)->findOneByCode($code);

        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->getJobRegistry()->get($jobInstance->getJobName());

        $this->setupMailOption($input->getOption('email'));

        $jobParameters = $this->getJobParameters($job, $jobInstance, $input->getOption('config'));

        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $this->getDefaultEntityManager()->merge($jobInstance);

        $this->validateJobParameters($job, $jobParameters, $code);

        $this->getDefaultEntityManager()->clear(get_class($jobInstance));

        $executionId = $input->getArgument('execution');

        $jobExecution = $this->getConfiguredJobExecution($job, $jobInstance, $jobParameters, $executionId);

        $job->execute($jobExecution);

        $job->getJobRepository()->updateJobExecution($jobExecution);

        $verbose = $input->getOption('verbose');

        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            return $this->getCompleteExitStatus($jobExecution, $jobInstance, $output, $verbose);
        }

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

        return self::EXIT_ERROR_CODE;
    }

    /**
     * @param JobInterface $job
     * @param JobInstance $jobInstance
     * @param JobParameters $jobParameters
     * @param string $executionId
     *
     * @return JobExecution|object
     */
    private function getConfiguredJobExecution(
        JobInterface $job,
        JobInstance $jobInstance,
        JobParameters $jobParameters,
        $executionId = null
    ) {
        $jobExecution = null;

        if (null === $executionId) {
            $jobExecution = $job->getJobRepository()->createJobExecution($jobInstance, $jobParameters);
        } else {
            $jobExecution = $this->getJobManager()->getRepository(JobExecution::class)->find($executionId);

            if (null === $jobExecution) {
                throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
            }

            $jobExecution->setJobParameters($jobParameters);

            if (!$jobExecution->getStatus()->isStarting()) {
                throw new \RuntimeException(
                    sprintf('Job execution "%s" has invalid status: %s', $executionId, $jobExecution->getStatus())
                );
            }

            if (null === $jobExecution->getExecutionContext()) {
                $jobExecution->setExecutionContext(new ExecutionContext());
            }
        }


        $jobExecution->setJobInstance($jobInstance);
        $jobExecution->setPid(getmypid());
        $this->getBatchLogHandler()->setSubDirectory($jobExecution->getId());

        return $jobExecution;
    }

    /**
     * @param JobInterface $job
     * @param JobInstance $jobInstance
     * @param array $config
     *
     * @return JobParameters
     */
    private function getJobParameters(JobInterface $job, JobInstance $jobInstance, $config = null)
    {
        $jobParamsFactory = $this->getJobParametersFactory();
        $rawParameters = $jobInstance->getRawParameters();
        if (null !== $config) {
            $rawParameters = array_merge($rawParameters, $this->decodeConfiguration($config));
        }

        return $jobParamsFactory->create($job, $rawParameters);
    }

    /**
     * @param JobInterface $job
     * @param JobParameters $jobParameters
     * @param string $jobInstanceCode
     */
    private function validateJobParameters(JobInterface $job, JobParameters $jobParameters, $jobInstanceCode)
    {
        $errors = $this->getJobParametersValidator()->validate($job, $jobParameters, ['Default', 'Execution']);

        if (count($errors) > 0) {
            throw new \RuntimeException(
                sprintf(
                    'Job instance "%s" running the job "%s" with parameters "%s" is invalid because of "%s"',
                    $jobInstanceCode,
                    $job->getName(),
                    print_r($jobParameters->all(), true),
                    $this->getErrorMessages($errors)
                )
            );
        }
    }

    /**
     * @param null $email
     */
    private function setupMailOption($email = null)
    {
        if (null === $email) {
            return;
        }

        // Override mail notifier recipient email
        $errors = $this->getValidator()->validateValue($email, new Assert\Email());
        if (count($errors) > 0) {
            throw new \RuntimeException(
                sprintf('Email "%s" is invalid: %s', $email, $this->getErrorMessages($errors))
            );
        }

        $this->getMailNotifier()->setRecipientEmail($email);
    }

    /**
     * @param JobExecution $jobExecution
     * @param JobInstance $jobInstance
     * @param OutputInterface $output
     * @param bool $verbose
     *
     * @return int
     */
    private function getCompleteExitStatus(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        OutputInterface $output,
        $verbose = false
    ) {
        $nbWarnings = array_reduce($jobExecution->getStepExecutions(), function ($carry, StepExecution $stepExecution) {
            return $carry + count($stepExecution->getWarnings());
        }, 0);

        if (0 === $nbWarnings) {
            $output->writeln(
                sprintf(
                    '<info>%s %s has been successfully executed.</info>',
                    ucfirst($jobInstance->getType()),
                    $jobInstance->getCode()
                )
            );

            return self::EXIT_SUCCESS_CODE;
        }

        if ($verbose) {
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                foreach ($stepExecution->getWarnings() as $warning) {
                    $output->writeln(sprintf('<comment>%s</comment>', $warning->getReason()));
                }
            }
        }

        $output->writeln(
            sprintf(
                '<comment>%s %s has been executed with %d warnings.</comment>',
                ucfirst($jobInstance->getType()),
                $jobInstance->getCode(),
                $nbWarnings
            )
        );

        return self::EXIT_WARNING_CODE;
    }

    /**
     * Writes failure exceptions to the output
     *
     * @param OutputInterface $output
     * @param array[]         $exceptions
     * @param boolean         $verbose
     */
    protected function writeExceptions(OutputInterface $output, array $exceptions, $verbose)
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
    protected function getJobManager()
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }

    /**
     * @return EntityManager
     */
    protected function getDefaultEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return BatchLogHandler
     */
    protected function getBatchLogHandler()
    {
        return $this->getContainer()->get('akeneo_batch.logger.batch_log_handler');
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return Validator
     */
    protected function getMailNotifier()
    {
        return $this->getContainer()->get('akeneo_batch.mail_notifier');
    }

    /**
     * @return JobRegistry
     */
    protected function getJobRegistry()
    {
        return $this->getContainer()->get('akeneo_batch.job.job_registry');
    }

    /**
     * @return JobParametersFactory
     */
    protected function getJobParametersFactory()
    {
        return $this->getContainer()->get('akeneo_batch.job_parameters_factory');
    }

    /**
     * @return JobParametersValidator
     */
    protected function getJobParametersValidator()
    {
        return $this->getContainer()->get('akeneo_batch.job.job_parameters_validator');
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return string
     */
    private function getErrorMessages(ConstraintViolationList $errors)
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
     * @return array
     */
    private function decodeConfiguration($data)
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
}
