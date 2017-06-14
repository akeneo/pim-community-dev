<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Bundle\BatchBundle\Notification\MailNotifier;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchCommand extends Command
{
    const EXIT_SUCCESS_CODE = 0;
    const EXIT_ERROR_CODE = 1;
    const EXIT_WARNING_CODE = 2;

    /** @var DebugLoggerInterface */
    protected $logger;

    /** @var BatchLogHandler */
    protected $batchLogHandler;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepository;

    /** @var RegistryInterface */
    protected $doctrine;

    /** @var RecursiveValidator */
    protected $validator;

    /** @var MailNotifier */
    protected $mailNotifier;

    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var JobParametersValidator */
    protected $jobParametersValidator;

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param DebugLoggerInterface                  $logger
     * @param BatchLogHandler                       $batchLogHandler
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param RegistryInterface                     $doctrine
     * @param RecursiveValidator                    $validator
     * @param MailNotifier                          $notifier
     * @param JobParametersFactory                  $jobParametersFactory
     * @param JobParametersValidator                $jobParametersValidator
     * @param JobRegistry                           $jobRegistry
     * @param JobRepositoryInterface                $jobRepository
     */
    public function __construct(
        DebugLoggerInterface $logger,
        BatchLogHandler $batchLogHandler,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        RegistryInterface $doctrine,
        RecursiveValidator $validator,
        MailNotifier $notifier,
        JobParametersFactory $jobParametersFactory,
        JobParametersValidator $jobParametersValidator,
        JobRegistry $jobRegistry,
        JobRepositoryInterface $jobRepository
    ) {
        parent::__construct('akeneo:batch:job');

        $this->logger = $logger;
        $this->batchLogHandler = $batchLogHandler;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->mailNotifier = $notifier;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->jobRegistry = $jobRegistry;
        $this->jobRepository = $jobRepository;
    }

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
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $this->logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $code = $input->getArgument('code');
        $jobInstance = $this->jobRepository
            ->getJobManager()->getRepository('Akeneo\Component\Batch\Model\JobInstance')->findOneByCode($code);

        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $rawParameters = $jobInstance->getRawParameters();
        if ($config = $input->getOption('config')) {
            $rawParameters = array_merge($rawParameters, $this->decodeConfiguration($config));
        }
        $jobParameters = $this->jobParametersFactory->create($job, $rawParameters);

        // Override mail notifier recipient email
        if ($email = $input->getOption('email')) {
            $errors = $this->validator->validateValue($email, new Assert\Email());
            if (count($errors) > 0) {
                throw new \RuntimeException(
                    sprintf('Email "%s" is invalid: %s', $email, $this->getErrorMessages($errors))
                );
            }
            $this->mailNotifier->setRecipientEmail($email);
        }

        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $errors = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);
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

        $this->doctrine->getManager()->clear(get_class($jobInstance));

        $executionId = $input->getArgument('execution');
        if ($executionId) {
            $jobExecution = $this->jobRepository->getJobManager()
                ->getRepository('Akeneo\Component\Batch\Model\JobExecution')->find($executionId);
            $jobExecution->setJobParameters($jobParameters);
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
        } else {
            $jobExecution = $job->getJobRepository()->createJobExecution($jobInstance, $jobParameters);
        }
        $jobExecution->setJobInstance($jobInstance);

        $jobExecution->setPid(getmypid());

        $this->batchLogHandler->setSubDirectory($jobExecution->getId());

        $job->execute($jobExecution);

        $job->getJobRepository()->updateJobExecution($jobExecution);

        $verbose = $input->getOption('verbose');
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

                return self::EXIT_SUCCESS_CODE;
            } else {
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

            return self::EXIT_ERROR_CODE;
        }
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
