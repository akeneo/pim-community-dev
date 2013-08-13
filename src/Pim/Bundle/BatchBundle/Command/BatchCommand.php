<?php

namespace Pim\Bundle\BatchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Symfony\Component\Validator\ConstraintViolationList;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

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
            ->setName('pim:batch:job')
            ->setDescription('Launch a registered job')
            ->addArgument('code', InputArgument::REQUIRED, 'Job code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $job = $this->getEntityManager()->getRepository('PimBatchBundle:Job')->findOneByCode($code);
        if (!$job) {
            throw new \InvalidArgumentException(sprintf('Could not find job "%s".', $code));
        }

        $definition = $this->getConnectorRegistry()->getJob($job);
        $job->setJobDefinition($definition);

        $errors = $this->getValidator()->validate($job, array('Default', 'Execution'));
        if (count($errors) > 0) {
            throw new \RuntimeException(sprintf('Job "%s" is invalid: %s', $code, $this->getErrorMessages($errors)));
        }
        $jobExecution = new JobExecution;
        $jobExecution->setJob($job);
        $definition->execute($jobExecution);

        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            $output->writeln(sprintf('<info>%s has been successfully executed.</info>', ucfirst($job->getType())));
        } else {
            $output->writeln(sprintf('<error>An error occured during the %s execution.</error>', $job->getType()));
        }
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
        return $this->getContainer()->get('pim_batch.connectors');
    }

    private function getErrorMessages(ConstraintViolationList $errors)
    {
        $str = '';
        foreach ($errors as $error) {
            $str .= sprintf("\n  - %s", $error);
        }

        return $str;
    }
}
