<?php
declare(strict_types=1);

namespace AkeneoTest\Acceptance\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemorySpyTransport extends InMemoryTransport
{
    public const GET_EVENT = 'get_event';
    public const ACK_EVENT = 'ack_event';
    public const SEND_EVENT = 'send_event';

    private array $events = [];

    /**
     * {@inheritdoc}
     */
    public function get(): iterable
    {
        $this->events[] = self::GET_EVENT;
        return parent::get();
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        $this->events[] = self::ACK_EVENT;
        parent::ack($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        $this->events[] = self::SEND_EVENT;
        return parent::send($envelope);
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
