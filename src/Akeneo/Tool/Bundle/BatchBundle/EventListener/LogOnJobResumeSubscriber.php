<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogOnJobResumeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_STEP_EXECUTION_RESUME => 'log'
        ];
    }

    public function log(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->notice('Job has been resumed.', [
            'job_execution_id' => $stepExecution->getJobExecution()->getId(),
            'job_code' => $stepExecution->getJobExecution()->getJobInstance()->getCode(),
            'step_execution_id' => $stepExecution->getId(),
            'step_name' => $stepExecution->getStepName(),
            'current_state' => $stepExecution->getCurrentState(),
        ]);
    }
}
