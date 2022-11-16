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

use Google\Cloud\PubSub\Message as PubSubMessage;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use Webmozart\Assert\Assert;

final class Client
{
    private PubSubClient $pubSubClient;
    /** @var array<string, Topic> */
    private array $topics = [];
    private bool $isResourceCreationAllowed;

    public function __construct(PubSubClient $pubSubClient, bool $isResourceCreationAllowed)
    {
        $this->pubSubClient = $pubSubClient;
        $this->isResourceCreationAllowed = $isResourceCreationAllowed;
    }

    public function publish(string $topicName, PubSubMessage $message): void
    {
        $this->getTopic($topicName)->publish($message);
    }

    /**
     * @param PubSubMessage[] $messages
     */
    public function publishBatch(string $topicName, array $messages): void
    {
        Assert::allIsInstanceOf($messages, PubSubMessage::class);
        $this->getTopic($topicName)->publishBatch($messages);
    }

    private function getTopic(string $topicName): Topic
    {
        $topic = $this->topics[$topicName] ?? null;
        if (null === $topic) {
            $this->topics[$topicName] = $this->pubSubClient->topic($topicName);
            if ($this->isResourceCreationAllowed && !$this->topics[$topicName]->exists()) {
                $this->topics[$topicName]->create();
            }
        }

        return $this->topics[$topicName];
    }
}
