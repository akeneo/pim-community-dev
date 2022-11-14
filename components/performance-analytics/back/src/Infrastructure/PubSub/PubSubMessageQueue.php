<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Infrastructure\PubSub;

use Akeneo\PerformanceAnalytics\Application\LogContext;
use Akeneo\PerformanceAnalytics\Domain\Message;
use Akeneo\PerformanceAnalytics\Domain\MessageQueue;
use Google\Cloud\PubSub\Message as PubSubMessage;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class PubSubMessageQueue implements MessageQueue
{
    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
        private ?string $tenantId,
        private string $topicName
    ) {
    }

    public function publish(Message $message): void
    {
        if (null === $this->tenantId) {
            $this->logger->warning('Tenant ID is null', LogContext::build());
        }

        try {
            $this->client->publish($this->topicName, $this->buildPubSubMessage($message));
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error while sending message to PubSub: '.$e->getMessage(),
                LogContext::build(['message' => $message->normalize()])
            );

            throw $e;
        }
    }

    /**
     * @param Message[] $messages
     */
    public function publishBatch(array $messages): void
    {
        Assert::allIsInstanceOf($messages, Message::class);
        if (null === $this->tenantId) {
            $this->logger->warning('Tenant ID is null', LogContext::build());
        }

        try {
            $this->client->publishBatch(
                $this->topicName,
                \array_map(fn (Message $message): PubSubMessage => $this->buildPubSubMessage($message), $messages)
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error while sending messages to PubSub: '.$e->getMessage(),
                LogContext::build(['messages' => \array_map(fn (Message $message): array => $message->normalize(), $messages)])
            );

            throw $e;
        }
    }

    private function buildPubSubMessage(Message $message): PubSubMessage
    {
        return new PubSubMessage([
            'data' => \json_encode($message->normalize()),
            'attributes' => [
                'class' => \get_class($message),
                'tenant_id' => $this->tenantId ?? 'null',
            ],
        ]);
    }
}
