<?php
declare(strict_types=1);

namespace AkeneoTest\Integration\IntegrationTestsBundle\Launcher;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;

/**
 * This class provides some helping method to test the message in a pub/sub queue.
 * For testing purposes only.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PubSubQueueStatus
{
    public function __construct(
        private readonly PubSubClientFactory $pubSubClientFactory,
        private readonly string $projectId,
        private readonly string $topicName,
        private readonly string $subscriptionName
    ) {
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
                // so we can have message in the topic without having an existing subscription.
                $subscription->create();
            }

            $messages = $subscription->pull([
                'maxMessages' => 1,
                'returnImmediately' => true,
            ]);

            foreach ($messages as $message) {
                // From documentation: Specifying zero may immediately make the message available for another pull request
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

    public function getSubscription(): Subscription
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->projectId]);
        $topic = $pubSubClient->topic($this->topicName);

        return $topic->subscription($this->subscriptionName);
    }

    public function createTopicAndSubscription(): void
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->projectId]);
        $topic = $pubSubClient->topic($this->topicName);

        if (!$topic->exists()) {
            $topic->create();
        }

        $subscription = $topic->subscription($this->subscriptionName);
        if (!$subscription->exists()) {
            $subscription->create();
        }
    }
}
