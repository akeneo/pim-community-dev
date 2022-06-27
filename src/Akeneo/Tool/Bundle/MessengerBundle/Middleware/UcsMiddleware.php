<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class UcsMiddleware implements MiddlewareInterface
{
    public function __construct(private ?string $pimTenantId)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$this->pimTenantId) {
            return $stack->next()->handle($envelope, $stack);
        }

        if (null === $envelope->last(TenantIdStamp::class)) {
            $envelope = $envelope->with(new TenantIdStamp($this->pimTenantId));
        }

        if ($envelope->getMessage() instanceof TenantAwareInterface) {
            $envelope->getMessage()->setTenantId($this->pimTenantId);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
