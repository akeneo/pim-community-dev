<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
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

    public function setTranslationLocale($translationLocale)
    {
        $this->translationLocale = $translationLocale;
    }

    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    public function jobExecutionCreated(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution is created: %s', $jobExecution));
    }

    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Job execution starting: %s', $jobExecution));
    }

    public function jobExecutionStopped(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution was stopped: %s', $jobExecution));
    }

    public function jobExecutionInterrupted(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Encountered interruption executing job: %s', $jobExecution));
        $this->logger->debug('Full exception', ['exception', $jobExecution->getFailureExceptions()]);
    }

    public function jobExecutionFatalError(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->error(
            'Encountered fatal error executing job',
            ['exception', $jobExecution->getFailureExceptions()]
        );
    }

    public function beforeJobStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Upgrading JobExecution status: %s', $jobExecution));
    }

    public function beforeStepExecution(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution starting: %s', $stepExecution));
    }

    public function stepExecutionSucceeded(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution success: id= %d', $stepExecution->getId()));
    }

    public function stepExecutionInterrupted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(
            sprintf('Encountered interruption executing step: %s', $stepExecution->getFailureExceptionMessages())
        );
        $this->logger->debug('Full exception', ['exception', $stepExecution->getFailureExceptions()]);
    }

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

    public function stepExecutionCompleted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution complete: %s', $stepExecution));
    }

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
