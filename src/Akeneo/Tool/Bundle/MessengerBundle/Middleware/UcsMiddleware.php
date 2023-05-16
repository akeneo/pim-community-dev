<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UcsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ?string $pimTenantId,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // We always try to use the tenantid from the stamp, if there is any, in case of long-running process.
        // If there is none, we fallback on the tenantid coming from the env variables.
        $tenantId = $envelope->last(TenantIdStamp::class)?->pimTenantId() ?: $this->pimTenantId;

        if (empty($tenantId)) {
            $this->logger->warning(sprintf(
                'A message of type "%s" is consumed without a tenant id available',
                get_class($envelope->getMessage()),
            ));
        }

        if ($tenantId && null === $envelope->last(TenantIdStamp::class)) {
            $envelope = $envelope->with(new TenantIdStamp($tenantId));
        }

        if ($tenantId && $envelope->getMessage() instanceof TenantAwareInterface) {
            $envelope->getMessage()->setTenantId($tenantId);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
