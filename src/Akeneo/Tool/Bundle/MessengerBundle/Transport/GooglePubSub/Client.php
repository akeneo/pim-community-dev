<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Webmozart\Assert\Assert;

/**
 * Simple abstraction over the Google PubSubClient.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Client
{
    private Topic $topic;
    private ?Subscription $subscription = null;
    private array $subscriptionOptions = [];

    /**
     * @param string $dsn Must be `gps:`
     * @param array{
     *      project_id: string
     *      topic_name: string,
     *      subscription_name: ?string
     *      auto_setup: ?bool
     *      subscription_filter: ?string
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
            'filter' => null,
        ];
        $options = array_merge($defaultOptions, $options);
        $subscriptionOptions = isset($options['subscription_filter'])
            ? ['filter' => $options['subscription_filter']]
            : []
        ;

        Assert::string($options['project_id']);
        Assert::string($options['topic_name']);

        foreach (['project_id', 'topic_name'] as $key) {
            Assert::string($options[$key], sprintf('Option "%s" is missing or invalid.', $key));
        }

        $client = new self(
            $pubSubClientFactory,
            $options['project_id'],
            $options['topic_name'],
            $options['subscription_name'],
            $subscriptionOptions
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
        ?string $subscriptionName,
        array $subscriptionOptions = []
    ) {
        $this->subscriptionOptions = $subscriptionOptions;
        $pubSubClient = $pubSubClientFactory->createPubSubClient([
            'projectId' => $projectId
        ]);

        $this->topic = $pubSubClient->topic($topicName);
        if (null !== $subscriptionName) {
            $this->subscription = $this->topic->subscription($subscriptionName);
        }
    }

    public function setup(array $options = []): void
    {
        if (!$this->topic->exists()) {
            $this->topic->create();
        }

        if (null !== $this->subscription && !$this->subscription->exists()) {
//            print_r("Create [" . $this->subscription->name() . "] with:\n");
//            print_r($this->subscriptionOptions);
            $this->subscription->create($this->subscriptionOptions);
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
