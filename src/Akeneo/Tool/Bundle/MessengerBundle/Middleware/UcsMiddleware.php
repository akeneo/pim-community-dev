<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ConsumerNameStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy\MessageWrapper;
use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UcsMiddleware implements MiddlewareInterface
{
    public function __construct(private ?string $pimTenantId)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // No tenant ID in the env, but existing in the received message
        // We are in a daemon long-running process
        if (null === $this->pimTenantId && null !== $envelope->last(TenantIdStamp::class)) {
            /** @var TenantIdStamp $stamp */
            $stamp = $envelope->last(TenantIdStamp::class);
            $this->pimTenantId = $stamp->pimTenantId();
        }

        // Tenant ID exists in this env
        // We are in a contextualized process
        if ($this->pimTenantId && null === $envelope->last(TenantIdStamp::class)) {
            $envelope = $envelope->with(new TenantIdStamp($this->pimTenantId));
        }

        // Enrich the message with the tenant ID
        if ($this->pimTenantId && $envelope->getMessage() instanceof TenantAwareInterface) {
            $envelope->getMessage()->setTenantId($this->pimTenantId);
        }

        $consumerNameStamp = $envelope->last(ConsumerNameStamp::class);
        if (null !== $consumerNameStamp && $envelope->getMessage() instanceof MessageWrapper) {
            $envelope->getMessage()->setConsumerName((string) $consumerNameStamp);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
