<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

/**
 * Simple abstraction over the Google PubSubClient.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Client
{
    /** @var Topic */
    private $topic;

    /** @var ?Subscription */
    private $subscription;

    /**
     * @param string $dsn Must be `gps:`
     * @param array{
     *      project_id: string
     *      topic_name: string,
     *      subscription_name: ?string
     *      auto_setup: ?bool
     *  } $options
     */
    public static function fromDsn(
        PubSubClientFactory $pubSubClientFactory,
        string $dsn,
        array $options = []
    ): self {
        if (0 !== strpos($dsn, 'gps:')) {
            throw new \InvalidArgumentException(sprintf('DSN "%s" is invalid.', $dsn));
        }

        $defaultOptions = [
            'project_id' => null,
            'topic_name' => null,
            'subscription_name' => null,
            'auto_setup' => false,
        ];
        $options = array_merge($defaultOptions, $options);

        foreach (['project_id', 'topic_name'] as $key) {
            if (!is_string($options[$key])) {
                throw new \InvalidArgumentException(
                    sprintf('Option "%s" is missing or invalid.', $key)
                );
            }
        }

        $client = new self(
            $pubSubClientFactory,
            $options['project_id'],
            $options['topic_name'],
            $options['subscription_name']
        );

        if (true === $options['auto_setup']) {
            $client->setup();
        }

        return $client;
    }

    public function __construct(
        PubSubClientFactory $pubSubClientFactory,
        string $projectId,
        string $topicName,
        ?string $subscriptionName
    ) {
        $pubSubClient = $pubSubClientFactory->createPubSubClient([
            'projectId' => $projectId
        ]);

        $this->topic = $pubSubClient->topic($topicName);
        if (null !== $subscriptionName) {
            $this->subscription = $this->topic->subscription($subscriptionName);
        }
    }

    public function setup(): void
    {
        if (!$this->topic->exists()) {
            $this->topic->create();
        }

        if (null !== $this->subscription && !$this->subscription->exists()) {
            $this->subscription->create();
        }
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }
}
