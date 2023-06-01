<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Process\RunUcsMessageProcess;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleUcsMessageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RunUcsMessageProcess $runUcsMessageProcess,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        $consumerName = $envelope->last(ReceivedStamp::class)?->getTransportName();
        if (null === $consumerName) {
            throw new \LogicException('The envelope must have a consumer name from a ReceivedStamp');
        }

        $tenantId = $envelope->last(TenantIdStamp::class)?->pimTenantId();
        $correlationId = $envelope->last(CorrelationIdStamp::class)?->correlationId();

        try {
            ($this->runUcsMessageProcess)($message, $consumerName, $tenantId, $correlationId);
        } catch (\Throwable $e) {
            throw new HandlerFailedException($envelope, [$e]);
        }

        return $envelope;
    }
}
