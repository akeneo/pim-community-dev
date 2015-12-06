<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a JobInstance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJobCommand extends ContainerAwareCommand
{
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
            ->addArgument('config', InputArgument::REQUIRED, 'Job instance config')
            ->addArgument('label', InputArgument::OPTIONAL, 'Job instance label');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $input->getArgument('connector');
        $job = $input->getArgument('job');
        $type = $input->getArgument('type');
        $code = $input->getArgument('code');
        $label = $input->getArgument('label');
        $label = $label ? $label : $code;
        $jsonConfig = $input->getArgument('config');
        $rawConfig = json_decode($jsonConfig, true);

        $factory = $this->getJobInstanceFactory();
        $jobInstance = $factory->createJobInstance($type);
        $jobInstance->setConnector($connector);
        $jobInstance->setAlias($job);
        $jobInstance->setCode($code);
        $jobInstance->setLabel($label);
        $jobInstance->setRawConfiguration($rawConfig);

        $objectManager = $this->getObjectManager();
        $objectManager->persist($jobInstance);
        $objectManager->flush();
    }

    /**
     * @return JobInstanceFactory
     */
    protected function getJobInstanceFactory()
    {
        return $this->getContainer()->get('akeneo_batch.job_instance_factory');
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
