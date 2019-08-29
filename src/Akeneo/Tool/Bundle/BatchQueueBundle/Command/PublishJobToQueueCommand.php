<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $publishJobToQueue = $this->getContainer()->get('akeneo_batch_queue.queue.publish_job_to_queue');

        $jobInstanceCode = $input->getArgument('code');
        $config = $input->getOption('config') ? $this->decodeConfiguration($input->getOption('config')) : [];
        $noLog = $input->getOption('no-log') ? true : false;
        $username = $input->getOption('username');
        $email = $input->getOption('email');

        $publishJobToQueue->publish(
            $jobInstanceCode,
            $config,
            $noLog,
            $username,
            $email
        );

        $jobInstanceClass = $this->getContainer()->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this->getJobManager()->getRepository($jobInstanceClass)->findOneBy(['code' => $jobInstanceCode]);

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
    private function getJobManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }

    /**
     * @return JobRegistry
     */
    private function getJobRegistry(): JobRegistry
    {
        return $this->getContainer()->get('akeneo_batch.job.job_registry');
    }

    /**
     * @return JobParametersFactory
     */
    private function getJobParametersFactory(): JobParametersFactory
    {
        return $this->getContainer()->get('akeneo_batch.job_parameters_factory');
    }
}
