<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Messenger;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\Event;
use Symfony\Component\Messenger\TraceableMessageBus;

trait AssertEventCountTrait
{
    public function assertEventCount(int $expectedCount, string $eventClassName): void
    {
        if (!is_subclass_of($eventClassName, Event::class)) {
            throw new \LogicException(sprintf('%s is not a valid Event class', $eventClassName));
        }

        /** @var TraceableMessageBus $messageBus */
        $messageBus = $this->get('akeneo_integration_tests.message_bus_observer');

        $messages = $messageBus->getDispatchedMessages();
        $count = 0;

        foreach ($messages as $message) {
            $payload = $message['message'];
            if (!$payload instanceof BulkEventInterface) {
                continue;
            }
            foreach ($payload->getEvents() as $event) {
                if ($event instanceof $eventClassName) {
                    $count++;
                }
            }
        }

        $this->assertSame($expectedCount, $count);
    }
}
