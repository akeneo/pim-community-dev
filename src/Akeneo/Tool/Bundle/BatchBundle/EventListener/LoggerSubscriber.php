<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Subscriber to log job execution result
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class LoggerSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;
    protected TranslatorInterface $translator;
    protected string $translationLocale = 'en';
    protected string $translationDomain = 'messages';

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::JOB_EXECUTION_CREATED => 'jobExecutionCreated',
            EventInterface::BEFORE_JOB_EXECUTION => 'beforeJobExecution',
            EventInterface::JOB_EXECUTION_STOPPED => 'jobExecutionStopped',
            EventInterface::JOB_EXECUTION_INTERRUPTED => 'jobExecutionInterrupted',
            EventInterface::JOB_EXECUTION_FATAL_ERROR => 'jobExecutionFatalError',
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeJobStatusUpgrade',
            EventInterface::BEFORE_STEP_EXECUTION => 'beforeStepExecution',
            EventInterface::STEP_EXECUTION_SUCCEEDED => 'stepExecutionSucceeded',
            EventInterface::STEP_EXECUTION_INTERRUPTED => 'stepExecutionInterrupted',
            EventInterface::STEP_EXECUTION_ERRORED => 'stepExecutionErrored',
            EventInterface::STEP_EXECUTION_COMPLETED => 'stepExecutionCompleted',
            EventInterface::INVALID_ITEM => 'invalidItem',
        ];
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
     */
    public function jobExecutionCreated(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Job execution is created: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution before the job execution
     */
    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Job execution starting: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution when the job execution stopped
     */
    public function jobExecutionStopped(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log('info', sprintf('Job execution was stopped: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution when the job execution was interrupted
     */
    public function jobExecutionInterrupted(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log('info', sprintf('Encountered interruption executing job: %s', $jobExecution), $jobExecution);
        $this->log('debug', 'Full exception', $jobExecution, ['exception', $jobExecution->getFailureExceptions()]);
    }

    /**
     * Log the job execution when a fatal error was raised during job execution
     */
    public function jobExecutionFatalError(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log(
            'error',
            'Encountered fatal error executing job',
            $jobExecution,
            ['exception', $jobExecution->getFailureExceptions()]
        );
    }

    /**
     * Log the job execution before its status is upgraded
     */
    public function beforeJobStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Upgrading JobExecution status: %s', $jobExecution), $jobExecution);
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

        $this->logger->info(sprintf('Step execution complete: %s', $stepExecution));
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

    private function log(string $level, string $message, JobExecution $jobExecution, array $context = []): void
    {
        if (!isset($context['connector'])) {
            $context['connector'] = $jobExecution->getJobInstance()->getConnector();
        }
        if (!isset($context['jobname'])) {
            $context['jobname'] = $jobExecution->getJobInstance()->getJobName();
        }

        $this->logger->$level($message, $context);
    }

    private function formatAsString($data): string
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

        return (string)$data;
    }
}
