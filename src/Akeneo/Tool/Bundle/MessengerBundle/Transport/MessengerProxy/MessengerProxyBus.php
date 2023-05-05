<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerProxyBus implements MessageBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $decoratedMessageBus,
        private readonly string $tenantId
    ) {
    }

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $messageWrapper = MessageWrapper::create($message, $this->tenantId);

        return $this->decoratedMessageBus->dispatch($messageWrapper, $stamps);
    }
}
