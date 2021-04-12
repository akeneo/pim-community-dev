<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Integration\IntegrationTestsBundle\Launcher;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\PubSub\Message;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class PubSubStatus
{
    private PubSubClientFactory $pubSubClientFactory;
    private string $projectId;
    private string $topicName;
    private string $subscriptionName;

    public function __construct(
        PubSubClientFactory $pubSubClientFactory,
        string $projectId,
        string $topicName,
        string $subscriptionName
    ) {
        $this->pubSubClientFactory = $pubSubClientFactory;
        $this->projectId = $projectId;
        $this->topicName = $topicName;
        $this->subscriptionName = $subscriptionName;
    }

    public function hasMessageInQueue(): bool
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->projectId]);
        $topic = $pubSubClient->topic($this->topicName);
        $subscription = $topic->subscription($this->subscriptionName);


        try {
            if (!$topic->exists()) {
                return false;
            }

            if (!$subscription->exists()) {
                // We can have multiple subscription for one topic,
                // so we can have have message in the topic without having an existing subscription.
                $subscription->create();
            }

            $messages = $subscription->pull([
                'maxMessages' => 1,
                'returnImmediately' => true,
            ]);

            foreach ($messages as $message) {
                $subscription->modifyAckDeadline($message, 0);
            }

            return count($messages) > 0;
        } catch (ServiceException $exception) {
            throw new \RuntimeException(sprintf('Unable to access Pub/Sub: %s', $exception->getMessage()));
        }
    }

    /**
     * Returns the message in queue, without removing them.
     * For testing purposes only.
     *
     * @return Message[]
     */
    public function getMessagesInQueue(): array
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->projectId]);
        $topic = $pubSubClient->topic($this->topicName);
        $subscription = $topic->subscription($this->subscriptionName);


        try {
            if (!$topic->exists()) {
                return [];
            }

            if (!$subscription->exists()) {
                // We can have multiple subscription for one topic,
                // so we can have have message in the topic without having an existing subscription.
                $subscription->create();
            }

            $messages = $subscription->pull([
                'maxMessages' => 100,
                'returnImmediately' => true,
            ]);

            foreach ($messages as $message) {
                $subscription->modifyAckDeadline($message, 0);
            }

            return $messages;
        } catch (ServiceException $exception) {
            throw new \RuntimeException(sprintf('Unable to access Pub/Sub: %s', $exception->getMessage()));
        }
    }
}
