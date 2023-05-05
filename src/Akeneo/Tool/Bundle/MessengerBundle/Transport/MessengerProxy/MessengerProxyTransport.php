<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerProxyTransport implements TransportInterface
{
    public function __construct(
        private array $transportsByEnv,
        private TransportInterface $transportFallback,
        private string $env
    ) {
    }

    public function get(): iterable
    {
        throw new \LogicException('Transport is not designed to receive message');
    }

    public function ack(Envelope $envelope): void
    {
        $this->findTransport($envelope)->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->findTransport($envelope)->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->findTransport($envelope)->send($envelope);
    }

    private function findTransport(Envelope $envelope): TransportInterface
    {
        $message = $envelope->getMessage();

        // TODO logic
        return $this->transportFallback;
    }
}
