<?php

namespace Oro\Bundle\BatchBundle\Command;

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
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\ExitStatus;
use Oro\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Batch command
 *
 */
class BatchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:batch:job')
            ->setDescription('Launch a registered job instance')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('execution', InputArgument::OPTIONAL, 'Job execution id')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php app/console oro:batch:job -c \'[{"reader":{"filePath":"/tmp/foo.csv"}}]\' ' .
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
        $jobInstance = $this->getEntityManager()->getRepository('OroBatchBundle:JobInstance')->findOneByCode($code);
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

        $this->validate($input, $jobInstance);

        $executionId = $input->getArgument('execution');
        if ($executionId) {
            $jobExecution = $this->getEntityManager()->getRepository('OroBatchBundle:JobExecution')->find($executionId);
            if (!$jobExecution) {
                throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
            }
            if (!$jobExecution->getStatus()->isStarting()) {
                throw new \RuntimeException(
                    sprintf('Job execution "%s" has invalid status: %s', $executionId, $jobExecution->getStatus())
                );
            }
        } else {
            $jobExecution = new JobExecution();
        }
        $jobExecution->setJobInstance($jobInstance);

        $job->execute($jobExecution);

        $this->getEntityManager()->persist($jobInstance);
        $this->getEntityManager()->flush($jobInstance);

        $this->getEntityManager()->persist($jobExecution);
        $this->getEntityManager()->flush($jobExecution);

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
     * Validate job instance
     */
    protected function validate(InputInterface $input, JobInstance $jobInstance)
    {
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

        $errors = $validator->validate($jobInstance, array('Default', 'Execution'));
        if (count($errors) > 0) {
            throw new \RuntimeException(
                sprintf('Job "%s" is invalid: %s', $code, $this->getErrorMessages($errors))
            );
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
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
        return $this->getContainer()->get('oro_batch.mail_notifier');
    }

    /**
     * @return \Oro\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->getContainer()->get('oro_batch.connectors');
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
}
