<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Process\RunMessageProcess;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ReceiverStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleProcessMessageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RunMessageProcess $runMessageProcess,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var ReceivedStamp|null $receivedStamp */
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        if (null === $receivedStamp) {
            throw new \LogicException('The message can only be handled when received from transport');
        }

        /** @var ReceiverStamp $receiverStamp */
        $receiverStamp = $envelope->last(ReceiverStamp::class);
        if (null === $receiverStamp) {
            throw new \LogicException('The message can only be handled when received from transport');
        }

        /** @var RedeliveryStamp|null $redeliveryStamp */
        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);
        if (null !== $redeliveryStamp) {
            $retryCount = $redeliveryStamp->getRetryCount();
            $this->logger->notice('Message is retried', [
                'retry_count' => $retryCount,
                'transport_name' => $receivedStamp->getTransportName(),
            ]);
        }

        $tenantId = $envelope->last(TenantIdStamp::class)?->pimTenantId();
        $correlationId = $envelope->last(CorrelationIdStamp::class)?->correlationId();

        try {
            ($this->runMessageProcess)(
                $envelope,
                $receivedStamp->getTransportName(),
                $receiverStamp->receiver,
                $tenantId,
                $correlationId
            );
        } catch (\Throwable $e) {
            throw new HandlerFailedException($envelope, [$e]);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
