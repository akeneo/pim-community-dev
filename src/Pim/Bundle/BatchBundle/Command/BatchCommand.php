<?php

namespace Pim\Bundle\BatchBundle\Command;

use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setDescription('Launch a registered job instance')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addOption(
                'show-log',
                null,
                InputOption::VALUE_OPTIONAL,
                'display the log on the output'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');

        if (!$noDebug) {
            $logger = $this->getContainer()->get('logger');
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $code = $input->getArgument('code');
        $jobInstance = $this->getEntityManager()->getRepository('PimBatchBundle:JobInstance')->findOneByCode($code);
        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->getConnectorRegistry()->getJob($jobInstance);
        $jobInstance->setJob($job);

        $errors = $this->getValidator()->validate($jobInstance, array('Default', 'Execution'));
        if (count($errors) > 0) {
            throw new \RuntimeException(sprintf('Job "%s" is invalid: %s', $code, $this->getErrorMessages($errors)));
        }
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $job->execute($jobExecution);

        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            $output->writeln(
                sprintf(
                    '<info>%s has been successfully executed.</info>',
                    ucfirst($jobInstance->getType())
                )
            );
        } else {
            $output->writeln(
                sprintf(
                    '<error>An error occured during the %s execution.</error>',
                    $jobInstance->getType()
                )
            );
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
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }
}
