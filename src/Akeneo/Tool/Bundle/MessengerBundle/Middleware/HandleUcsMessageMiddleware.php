<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Handler\UcsEnvelopeMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleUcsMessageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly UcsEnvelopeMessageHandler $ucsEnvelopeMessageHandler,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            ($this->ucsEnvelopeMessageHandler)($envelope);
        } catch (\Throwable $e) {
            throw new HandlerFailedException($envelope, [$e]);
        }

        return $envelope;
    }
}
