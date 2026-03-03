<?php
declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Messenger;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\Event;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait AssertEventCountInTransportTrait
{
    protected array $envelopes = [];

    public function assertEventCount(int $expectedCount, string $eventClassName): void
    {
        if (!is_subclass_of($eventClassName, Event::class)) {
            throw new \LogicException(sprintf('%s is not a valid Event class', $eventClassName));
        }
        $this->pullMessages();

        $count = 0;
        foreach ($this->envelopes as $envelope) {
            $payload = $envelope->getMessage();
            if (!$payload instanceof BulkEventInterface) {
                continue;
            }
            foreach ($payload->getEvents() as $event) {
                if ($event instanceof $eventClassName) {
                    $count++;
                }
            }
        }

        $this->assertSame(
            $expectedCount,
            $count,
            sprintf(
                'Expecting to have %d event(s) of type "%s", but got %d.',
                $expectedCount,
                $eventClassName,
                $count
            )
        );
    }

    public function clearMessengerTransport(): void
    {
        $this->pullMessages();
        $this->envelopes = [];
    }

    private function pullMessages(): void
    {
        $transport = $this->get('messenger.transport.business_event');

        while (!empty($envelopes = $transport->get())) {
            foreach ($envelopes as $envelope) {
                $transport->ack($envelope);
                $this->envelopes[] = $envelope;
            }
        }
    }
}
