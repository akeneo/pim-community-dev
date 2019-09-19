<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Console\Command\Command;
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
class CreateJobCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:create-job';

    const EXIT_SUCCESS_CODE = 0;
    const EXIT_ERROR_CODE = 1;

    /** @var ValidatorInterface */
    private $validator;

    /** @var JobParametersValidator */
    private $jobParametersValidator;

    /** @var JobParametersFactory */
    private $jobParametersFactory;

    /** @var JobInstanceFactory */
    private $jobInstanceFactory;

    /** @var SaverInterface */
    private $jobInstanceSaver;

    /** @var JobRegistry */
    private $jobRegistry;

    public function __construct(
        ValidatorInterface $validator,
        JobParametersValidator $jobParametersValidator,
        JobParametersFactory $jobParametersFactory,
        JobInstanceFactory $jobInstanceFactory,
        SaverInterface $jobInstanceSaver,
        JobRegistry $jobRegistry
    ) {
        parent::__construct();

        $this->validator = $validator;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->jobInstanceSaver = $jobInstanceSaver;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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

        $factory = $this->jobInstanceFactory;
        $jobInstance = $factory->createJobInstance($type);
        $jobInstance->setConnector($connector);
        $jobInstance->setJobName($jobName);
        $jobInstance->setCode($code);
        $jobInstance->setLabel($label);
        $jobInstance->setRawParameters($rawConfig);

        /** @var JobInterface */
        $job = $this->jobRegistry->get($jobInstance->getJobName());
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
        $jobParameters = $this->jobParametersFactory->create($job, $rawConfig);
        $jobInstance->setRawParameters($jobParameters->all());

        $violations = $this->jobParametersValidator->validate($job, $jobParameters);
        if (count($violations) > 0) {
            $output->writeln(
                sprintf(
                    '<error>A validation error occurred with the job configuration "%s".</error>',
                    $this->getErrorMessages($violations)
                )
            );

            return self::EXIT_ERROR_CODE;
        }

        $violations = $this->validator->validate($jobInstance);
        if (count($violations) > 0) {
            $output->writeln(
                sprintf(
                    '<error>A validation error occurred while creating the job instance "%s".</error>',
                    $this->getErrorMessages($violations)
                )
            );

            return self::EXIT_ERROR_CODE;
        }

        $this->jobInstanceSaver->save($jobInstance);

        return self::EXIT_SUCCESS_CODE;
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
