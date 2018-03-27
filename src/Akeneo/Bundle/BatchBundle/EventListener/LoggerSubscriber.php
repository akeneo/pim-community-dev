<?php

namespace Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Event\StepExecutionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Subscriber to log job execution result
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class LoggerSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $translationLocale = 'en';

    /** @var string */
    protected $translationDomain = 'messages';

    /**
     * @param LoggerInterface     $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::JOB_EXECUTION_CREATED      => 'jobExecutionCreated',
            EventInterface::BEFORE_JOB_EXECUTION       => 'beforeJobExecution',
            EventInterface::JOB_EXECUTION_STOPPED      => 'jobExecutionStopped',
            EventInterface::JOB_EXECUTION_INTERRUPTED  => 'jobExecutionInterrupted',
            EventInterface::JOB_EXECUTION_FATAL_ERROR  => 'jobExecutionFatalError',
            EventInterface::BEFORE_JOB_STATUS_UPGRADE  => 'beforeJobStatusUpgrade',
            EventInterface::BEFORE_STEP_EXECUTION      => 'beforeStepExecution',
            EventInterface::STEP_EXECUTION_SUCCEEDED   => 'stepExecutionSucceeded',
            EventInterface::STEP_EXECUTION_INTERRUPTED => 'stepExecutionInterrupted',
            EventInterface::STEP_EXECUTION_ERRORED     => 'stepExecutionErrored',
            EventInterface::STEP_EXECUTION_COMPLETED   => 'stepExecutionCompleted',
            EventInterface::INVALID_ITEM               => 'invalidItem',
        );
    }

    /**
     * Set the translation locale
     *
     * @param string $translationLocale
     */
    public function setTranslationLocale($translationLocale)
    {
        $this->translationLocale = $translationLocale;
    }

    /**
     * Set the translation domain
     *
     * @param string $translationDomain
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * Log the job execution creation
     *
     * @param JobExecutionEvent $event
     */
    public function jobExecutionCreated(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution is created: %s', $jobExecution));
    }

    /**
     * Log the job execution before the job execution
     *
     * @param JobExecutionEvent $event
     */
    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution starting: %s', $jobExecution));
    }

    /**
     * Log the job execution when the job execution stopped
     *
     * @param JobExecutionEvent $event
     */
    public function jobExecutionStopped(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution was stopped: %s', $jobExecution));
    }

    /**
     * Log the job execution when the job execution was interrupted
     *
     * @param JobExecutionEvent $event
     */
    public function jobExecutionInterrupted(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Encountered interruption executing job: %s', $jobExecution));
        $this->logger->debug('Full exception', ['exception', $jobExecution->getFailureExceptions()]);
    }

    /**
     * Log the job execution when a fatal error was raised during job execution
     *
     * @param JobExecutionEvent $event
     */
    public function jobExecutionFatalError(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->error(
            'Encountered fatal error executing job',
            ['exception', $jobExecution->getFailureExceptions()]
        );
    }

    /**
     * Log the job execution before its status is upgraded
     *
     * @param JobExecutionEvent $event
     */
    public function beforeJobStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Upgrading JobExecution status: %s', $jobExecution));
    }

    /**
     * Log the step execution before the step execution
     *
     * @param StepExecutionEvent $event
     */
    public function beforeStepExecution(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution starting: %s', $stepExecution));
    }

    /**
     * Log the step execution when the step execution succeeded
     *
     * @param StepExecutionEvent $event
     */
    public function stepExecutionSucceeded(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->debug(sprintf('Step execution success: id= %d', $stepExecution->getId()));
    }

    /**
     * Log the step execution when the step execution was interrupted
     *
     * @param StepExecutionEvent $event
     */
    public function stepExecutionInterrupted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(
            sprintf('Encountered interruption executing step: %s', $stepExecution->getFailureExceptionMessages())
        );
        $this->logger->debug('Full exception', ['exception', $stepExecution->getFailureExceptions()]);
    }

    /**
     * Log the step execution when the step execution was errored
     *
     * @param StepExecutionEvent $event
     *
     * @return null
     */
    public function stepExecutionErrored(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->error(
            sprintf(
                'Encountered an error executing the step: %s',
                implode(
                    ', ',
                    array_map(
                        function ($exception) {
                            return $this->translator->trans(
                                $exception['message'],
                                $exception['messageParameters'],
                                $this->translationDomain,
                                $this->translationLocale
                            );
                        },
                        $stepExecution->getFailureExceptions()
                    )
                )
            )
        );
    }

    /**
     * Log the step execution when the step execution was completed
     *
     * @param StepExecutionEvent $event
     */
    public function stepExecutionCompleted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->debug(sprintf('Step execution complete: %s', $stepExecution));
    }

    /**
     * Log invalid item event
     *
     * @param InvalidItemEvent $event
     */
    public function invalidItem(InvalidItemEvent $event)
    {
        $this->logger->warning(
            sprintf(
                'The %s was unable to handle the following item: %s (REASON: %s)',
                $event->getClass(),
                $this->formatAsString($event->getItem()->getInvalidData()),
                $this->translator->trans(
                    $event->getReason(),
                    $event->getReasonParameters(),
                    $this->translationDomain,
                    $this->translationLocale
                )
            )
        );
    }

    /**
     * Format anything as a string
     *
     * @param mixed $data
     *
     * @return string
     */
    private function formatAsString($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[] = sprintf(
                    '%s => %s',
                    $this->formatAsString($key),
                    $this->formatAsString($value)
                );
            }

            return sprintf("[%s]", join(', ', $result));
        }

        if (is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        if ($data instanceof \DateTime) {
            return $data->format('Y-m-d');
        }

        return (string) $data;
    }
}
