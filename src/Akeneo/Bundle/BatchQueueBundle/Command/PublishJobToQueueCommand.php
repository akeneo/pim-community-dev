<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\Command;

use Akeneo\Bundle\BatchBundle\Notification\MailNotifier;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Push a registered job instance to execute into the job execution queue.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishJobToQueueCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'akeneo:batch:publish-job-to-queue';
    public const EXIT_SUCCESS_CODE = 0;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Publish a registered job instance to execute into the job execution queue')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php bin/console publish-job-to-queue -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username to launch the job instance with'
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
        $code = $input->getArgument('code');
        $jobInstanceClass = $this->getContainer()->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this->getJobManager()->getRepository($jobInstanceClass)->findOneBy(['code' => $code]);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $options = ['env' => $this->getContainer()->getParameter('kernel.environment')];
        $validator = $this->getValidator();
        $email = $input->getOption('email');

        if (null !== $email) {
            $errors = $validator->validate($email, new Assert\Email());
            if (count($errors) > 0) {
                throw new \RuntimeException(
                    sprintf('Email "%s" is invalid: %s', $email, $this->getErrorMessages($errors))
                );
            }
            $options['email'] = $email;
        }

        $noLog = $input->getOption('no-log');

        if (true === $noLog) {
            $options['no-log'] = true;
        }

        $job = $this->getJobRegistry()->get($jobInstance->getJobName());
        $jobParameters = $this->createJobParameters($jobInstance, $input);
        $this->validateJobParameters($jobInstance, $jobParameters, $code);
        $jobExecution = $this->getJobRepository()->createJobExecution($jobInstance, $jobParameters);

        $username = $input->getOption('username');
        if (null !== $username) {
            $jobExecution->setUser($username);
            $this->getJobRepository()->updateJobExecution($jobExecution);
        }

        $this->getJobRepository()->updateJobExecution($jobExecution);

        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);

        $this->getJobExecutionQueue()->publish($jobExecutionMessage);

        $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_CREATED, $jobExecution);

        $output->writeln(
            sprintf(
                '<info>%s %s has been successfully pushed into the queue.</info>',
                ucfirst($jobInstance->getType()),
                $jobInstance->getCode()
            )
        );

        return self::EXIT_SUCCESS_CODE;
    }

    /**
     * Trigger event linked to JobExecution
     *
     * @param string       $eventName    Name of the event
     * @param JobExecution $jobExecution Object to store job execution
     */
    private function dispatchJobExecutionEvent($eventName, JobExecution $jobExecution)
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->getContainer()->get('event_dispatcher')->dispatch($eventName, $event);
    }

    /**
     * @param JobInstance    $jobInstance
     * @param InputInterface $input
     *
     * @return JobParameters
     */
    protected function createJobParameters(JobInstance $jobInstance, InputInterface $input): JobParameters
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
    protected function validateJobParameters(JobInstance $jobInstance, JobParameters $jobParameters, string $code) : void
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

        $this->getDefaultEntityManager()->clear(ClassUtils::getClass($jobInstance));
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return string
     */
    private function getErrorMessages(ConstraintViolationList $errors): string
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
    private function decodeConfiguration($data): array
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
     * @return EntityManagerInterface
     */
    protected function getJobManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getDefaultEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return JobRegistry
     */
    protected function getJobRegistry(): JobRegistry
    {
        return $this->getContainer()->get('akeneo_batch.job.job_registry');
    }

    /**
     * @return JobParametersFactory
     */
    protected function getJobParametersFactory(): JobParametersFactory
    {
        return $this->getContainer()->get('akeneo_batch.job_parameters_factory');
    }

    /**
     * @return JobParametersValidator
     */
    protected function getJobParametersValidator(): JobParametersValidator
    {
        return $this->getContainer()->get('akeneo_batch.job.job_parameters_validator');
    }

    /**
     * @return JobRepositoryInterface
     */
    protected function getJobRepository(): JobRepositoryInterface
    {
        return $this->getContainer()->get('akeneo_batch.job_repository');
    }

    /**
     * @return JobExecutionQueueInterface
     */
    protected function getJobExecutionQueue(): JobExecutionQueueInterface
    {
        return $this->getContainer()->get('akeneo_batch_queue.queue.database_job_execution_queue');
    }
}
