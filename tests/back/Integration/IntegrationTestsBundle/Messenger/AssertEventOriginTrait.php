<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Messenger;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\Event;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\TraceableMessageBus;

trait AssertEventOriginTrait
{
    public function assertEventOrigin(string $origin): void
    {
        /** @var TraceableMessageBus $messageBus */
        $messageBus = $this->get('akeneo_integration_tests.message_bus_observer');

        $messages = $messageBus->getDispatchedMessages();

        foreach ($messages as $message) {
            $payload = $message['message'];
            if (!$payload instanceof BulkEventInterface) {
                continue;
            }
            foreach ($payload->getEvents() as $event) {
                /** @var Event $event */
                Assert::assertEquals($origin, $event->getOrigin());
            }
        }
    }
}
