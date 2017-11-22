<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a JobInstance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJobCommand extends ContainerAwareCommand
{
    const EXIT_SUCCESS_CODE = 0;
    const EXIT_ERROR_CODE = 1;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:batch:create-job')
            ->setDescription('Create a job instance')
            ->addArgument('connector', InputArgument::REQUIRED, 'Connector code')
            ->addArgument('job', InputArgument::REQUIRED, 'Job name')
            ->addArgument('type', InputArgument::REQUIRED, 'Job type')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('config', InputArgument::OPTIONAL, 'Job default parameters')
            ->addArgument('label', InputArgument::OPTIONAL, 'Job instance label');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $input->getArgument('connector');
        $jobName = $input->getArgument('job');
        $type = $input->getArgument('type');
        $code = $input->getArgument('code');
        $label = $input->getArgument('label');
        $label = $label ? $label : $code;
        $jsonConfig = $input->getArgument('config');
        $rawConfig = null === $jsonConfig ? [] : json_decode($jsonConfig, true);
        $jsonError = json_last_error_msg();
        if (null !== $jsonConfig && null !== $jsonError) {
            $output->writeln(
                sprintf(
                    '<error>config JSON decoding error: "%s"</error>',
                    $jsonError
                )
            );

            return self::EXIT_ERROR_CODE;
        }
        $factory = $this->getJobInstanceFactory();
        $jobInstance = $factory->createJobInstance($type);
        $jobInstance->setConnector($connector);
        $jobInstance->setJobName($jobName);
        $jobInstance->setCode($code);
        $jobInstance->setLabel($label);
        $jobInstance->setRawParameters($rawConfig);

        /** @var JobInterface */
        $job = $this->getJobRegistry()->get($jobInstance->getJobName());
        if (null === $job) {
            $output->writeln(
                sprintf(
                    '<error>Job "%s" does not exists.</error>',
                    $jobName
                )
            );

            return self::EXIT_ERROR_CODE;
        }

        /** @var JobParameters $jobParameters */
        $jobParameters = $this->getJobParametersFactory()->create($job, $rawConfig);
        $jobInstance->setRawParameters($jobParameters->all());

        $violations = $this->getJobParametersValidator()->validate($job, $jobParameters);
        if (count($violations) > 0) {
            $output->writeln(
                sprintf(
                    '<error>A validation error occurred with the job configuration "%s".</error>',
                    $this->getErrorMessages($violations)
                )
            );

            return self::EXIT_ERROR_CODE;
        }

        $violations = $this->getValidator()->validate($jobInstance);
        if (count($violations) > 0) {
            $output->writeln(
                sprintf(
                    '<error>A validation error occurred while creating the job instance "%s".</error>',
                    $this->getErrorMessages($violations)
                )
            );

            return self::EXIT_ERROR_CODE;
        }

        $this->getJobInstanceSaver()->save($jobInstance);

        return self::EXIT_SUCCESS_CODE;
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return JobParametersValidator
     */
    protected function getJobParametersValidator()
    {
        return $this->getContainer()->get('akeneo_batch.job.job_parameters_validator');
    }

    /**
     * @return JobParametersFactory
     */
    protected function getJobParametersFactory()
    {
        return $this->getContainer()->get('akeneo_batch.job_parameters_factory');
    }

    /**
     * @return JobInstanceFactory
     */
    protected function getJobInstanceFactory()
    {
        return $this->getContainer()->get('akeneo_batch.job_instance_factory');
    }

    /**
     * @return SaverInterface
     */
    protected function getJobInstanceSaver()
    {
        return $this->getContainer()->get('akeneo_batch.saver.job_instance');
    }

    /**
     * @return JobRegistry
     */
    protected function getJobRegistry()
    {
        return $this->getContainer()->get('akeneo_batch.job.job_registry');
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return string
     */
    protected function getErrorMessages(ConstraintViolationListInterface $errors)
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }
}
