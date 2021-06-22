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
    public function setTranslationLocale($translationLocale): void
    {
        $this->translationLocale = $translationLocale;
    }

    /**
     * Set the translation domain
     *
     * @param string $translationDomain
     */
    public function setTranslationDomain($translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * Log the job execution creation
     */
    public function jobExecutionCreated(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Job execution is created: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution before the job execution
     */
    public function beforeJobExecution(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Job execution starting: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution when the job execution stopped
     */
    public function jobExecutionStopped(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log('info', sprintf('Job execution was stopped: %s', $jobExecution), $jobExecution);
    }

    /**
     * Log the job execution when the job execution was interrupted
     */
    public function jobExecutionInterrupted(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log('info', sprintf('Encountered interruption executing job: %s', $jobExecution), $jobExecution);
        $this->log('debug', 'Full exception', $jobExecution, ['exception', $jobExecution->getFailureExceptions()]);
    }

    /**
     * Log the job execution when a fatal error was raised during job execution
     */
    public function jobExecutionFatalError(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log(
            'error',
            'Encountered fatal error executing job',
            $jobExecution,
            ['exception', $jobExecution->getFailureExceptions()]
        );
    }

    public function beforeJobStatusUpgrade(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        $this->log('debug', sprintf('Upgrading JobExecution status: %s', $jobExecution), $jobExecution);
    }

    public function beforeStepExecution(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->log('info', sprintf('Step execution starting: %s', $stepExecution), $stepExecution->getJobExecution());
    }

    public function stepExecutionSucceeded(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->log(
            'debug',
            sprintf('Step execution success: id= %d', $stepExecution->getId()),
            $stepExecution->getJobExecution()
        );
    }

    public function stepExecutionInterrupted(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->log(
            'info',
            sprintf('Encountered interruption executing step: %s', $stepExecution->getFailureExceptionMessages()),
            $stepExecution->getJobExecution()
        );
        $this->log(
            'debug',
            'Full exception',
            $stepExecution->getJobExecution(),
            ['exception', $stepExecution->getFailureExceptions()]
        );
    }

    public function stepExecutionErrored(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->log(
            'error',
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
            ),
            $stepExecution->getJobExecution()
        );
    }

    public function stepExecutionCompleted(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->log('info', sprintf('Step execution complete: %s', $stepExecution), $stepExecution->getJobExecution());
    }

    public function invalidItem(InvalidItemEvent $event): void
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
