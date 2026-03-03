<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Subscriber\UnableToSetIdentifiersSubscriberInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Event\UnableToSetIdentifierEvent;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnableToSetIdentifiersSubscriber implements EventSubscriberInterface, UnableToSetIdentifiersSubscriberInterface
{
    /** @var UnableToSetIdentifierEvent[] */
    private array $events = [];

    public function __construct(private JobRepositoryInterface $jobRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UnableToSetIdentifierEvent::class => 'storeEvent',
            EventInterface::ITEM_STEP_AFTER_BATCH => 'writeWarnings',
        ];
    }

    public function writeWarnings(StepExecutionEvent $stepExecutionEvent): void
    {
        $stepExecution = $stepExecutionEvent->getStepExecution();
        $warnings = [];
        foreach ($this->events as $event) {
            $exception = $event->getException();
            $warnings[]= new Warning(
                $stepExecution,
                $exception->getMessage(),
                [],
                $exception->getInvalidData()
            );
        }

        $this->jobRepository->addWarnings($stepExecution, $warnings);

        $this->events = [];
    }

    public function storeEvent(UnableToSetIdentifierEvent $unableToSetIdentifierEvent): void
    {
        $this->events[] = $unableToSetIdentifierEvent;
    }

    /**
     * @inheritDoc
     */
    public function getEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
