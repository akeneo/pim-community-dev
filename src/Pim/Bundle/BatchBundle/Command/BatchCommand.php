<?php

namespace Pim\Bundle\BatchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:job:launch')
            ->setDescription('Launch a configured jobInstance instance')
            ->addArgument('code', InputArgument::REQUIRED, 'jobInstance instance code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $jobInstance = $this->getEntityManager()->getRepository('PimBatchBundle:JobInstance')->findOneByCode($code);
        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->getConnectorRegistry()->getJob($jobInstance);
        // FIXME Usefull?
        $jobInstance->setJobDefinition($job);

        $errors = $this->getValidator()->validate($jobInstance, array('Default', 'Execution'));
        if (count($errors) > 0) {
            throw new \RuntimeException(sprintf('jobInstance instance "%s" is invalid: %s', $code, $this->getErrorMessages($errors)));
        }
        $this->getJobLauncher()->launch($job);

        $collector = $this->getJobDataCollector();
        $output->writeln(sprintf('<info>Reader executed %d time(s)</info>', $collector->getReaderExecutionCount()));
        $output->writeln(sprintf('<info>Processor executed %d time(s)</info>', $collector->getProcessorExecutionCount()));
        $output->writeln(sprintf('<info>Writer executed %d time(s)</info>', $collector->getWriterExecutionCount()));
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getEntityManager();
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return \Pim\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->getContainer()->get('pim_batch.connectors_registry');
    }

    protected function getJobLauncher()
    {
        return $this->getContainer()->get('pim_batch.job_launcher');
    }

    protected function getJobDataCollector()
    {
        return $this->getContainer()->get('pim_batch.job_data_collector');
    }

    private function getErrorMessages(ConstraintViolationList $errors)
    {
        return implode("\n - ", $errors);
    }
}
