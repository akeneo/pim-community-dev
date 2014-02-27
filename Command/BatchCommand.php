<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Constraints as Assert;
use Monolog\Handler\StreamHandler;
use Doctrine\ORM\EntityManager;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchCommand extends ContainerAwareCommand
{
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
                'php app/console akeneo:batch:job -c \'[{"reader":{"filePath":"/tmp/foo.csv"}}]\' ' .
                'acme_product_import)'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');
        if (!$noDebug) {
            $logger = $this->getContainer()->get('monolog.logger.batch');
            // Fixme: Use ConsoleHandler available on next Symfony version (2.4 ?)
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $code = $input->getArgument('code');
        $jobInstance = $this->getJobManager()->getRepository('AkeneoBatchBundle:JobInstance')->findOneByCode($code);
        if (!$jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $code));
        }

        $job = $this->getConnectorRegistry()->getJob($jobInstance);
        $jobInstance->setJob($job);

        // Override job configuration
        if ($config = $input->getOption('config')) {
            $job->setConfiguration(
                $this->decodeConfiguration($config)
            );
        }

        $validator = $this->getValidator();

        // Override mail notifier recipient email
        if ($email = $input->getOption('email')) {
            $errors = $validator->validateValue($email, new Assert\Email());
            if (count($errors) > 0) {
                throw new \RuntimeException(
                    sprintf('Email "%s" is invalid: %s', $email, $this->getErrorMessages($errors))
                );
            }
            $this
                ->getMailNotifier()
                ->setRecipientEmail($email);
        }

        // We merge the JobInstance from the JobManager EntitManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $defaultJobInstance = $this->getDefaultEntityManager()->merge($jobInstance);
        $defaultJobInstance->setJob($job);

        $errors = $validator->validate($defaultJobInstance, array('Default', 'Execution'));
        if (count($errors) > 0) {
            throw new \RuntimeException(
                sprintf('Job "%s" is invalid: %s', $code, $this->getErrorMessages($errors))
            );
        }

        $executionId = $input->getArgument('execution');
        if ($executionId) {
            $jobExecution = $this->getJobManager()->getRepository('AkeneoBatchBundle:JobExecution')->find($executionId);
            if (!$jobExecution) {
                throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
            }
            if (!$jobExecution->getStatus()->isStarting()) {
                throw new \RuntimeException(
                    sprintf('Job execution "%s" has invalid status: %s', $executionId, $jobExecution->getStatus())
                );
            }
        } else {
            $jobExecution = $job->getJobRepository()->createJobExecution($jobInstance);
        }
        $jobExecution->setJobInstance($jobInstance);

        $this
            ->getContainer()
            ->get('akeneo_batch.logger.batch_log_handler')
            ->setSubDirectory($jobExecution->getId());

        $job->execute($jobExecution);

        $job->getJobRepository()->updateJobExecution($jobExecution);

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

        // FIXME: Workaround, waiting for https://github.com/symfony/SwiftmailerBundle/pull/64
        // to be merged
        $this->flushMailQueue();
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
     * @return Validator
     */
    protected function getMailNotifier()
    {
        return $this->getContainer()->get('akeneo_batch.mail_notifier');
    }

    /**
     * @return \Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->getContainer()->get('akeneo_batch.connectors');
    }

    private function getErrorMessages(ConstraintViolationList $errors)
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }

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

    /**
     * @see Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener::onKernelTerminate
     * and https://github.com/symfony/SwiftmailerBundle/pull/64
     */
    public function flushMailQueue()
    {
        if (!$this->getContainer()->has('mailer')) {
            return;
        }

        $mailers = array_keys($this->getContainer()->getParameter('swiftmailer.mailers'));
        foreach ($mailers as $name) {
            if ($this->getContainer() instanceof IntrospectableContainerInterface ?
                $this->getContainer()->initialized(sprintf('swiftmailer.mailer.%s', $name)) : true) {
                if ($this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name))) {
                    $mailer = $this->getContainer()->get(sprintf('swiftmailer.mailer.%s', $name));
                    $transport = $mailer->getTransport();
                    if ($transport instanceof \Swift_Transport_SpoolTransport) {
                        $spool = $transport->getSpool();
                        if ($spool instanceof \Swift_MemorySpool) {
                            $spool->flushQueue(
                                $this->getContainer()->get(sprintf('swiftmailer.mailer.%s.transport.real', $name))
                            );
                        }
                    }
                }
            }
        }
 
    }
}
