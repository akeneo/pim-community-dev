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
    private const BATCH_SIZE = 500;

    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
        private ?string $tenantId,
        private string $topicName,
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
        if ([] === $messages) {
            return;
        }

        Assert::allIsInstanceOf($messages, Message::class);
        if (null === $this->tenantId) {
            $this->logger->warning('Tenant ID is null', LogContext::build());
        }

        $pubSubMessages = \array_map(
            fn (Message $message): PubSubMessage => $this->buildPubSubMessage($message),
            $messages
        );

        foreach (\array_chunk($pubSubMessages, self::BATCH_SIZE) as $pubSubMessagesBatch) {
            $try = 1;
            $isSuccess = false;
            do {
                try {
                    $this->client->publishBatch($this->topicName, $pubSubMessagesBatch);
                    $isSuccess = true;
                } catch (\Throwable $exception) {
                    $this->logger->error(
                        'Error while sending messages to PubSub: '.$exception->getMessage(),
                        LogContext::build([
                            'messages' => \array_map(
                                fn (PubSubMessage $message): string => $message->data(),
                                $pubSubMessagesBatch
                            ),
                            'try' => $try,
                        ])
                    );

                    ++$try;
                    if ($try >= 3) {
                        throw $exception;
                    }

                    usleep(100000 * $try); // 100000 = 100ms
                }
            } while (!$isSuccess);
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
