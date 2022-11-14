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

namespace Akeneo\Test\PerformanceAnalytics\Integration\PubSub;

use Akeneo\PerformanceAnalytics\Domain\Message;
use Akeneo\PerformanceAnalytics\Infrastructure\PubSub\PubSubMessageQueue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\PubSub\Subscription;

final class PubSubMessageQueueIntegration extends TestCase
{
    private PubSubMessageQueue $enrichedProductsQueue;
    private Subscription $enrichedProductsSubscription;

    /**
     * {@inheritDoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->enrichedProductsQueue = $this->get('akeneo.performance_analytics.queue.enriched_products');

        $client = Client::fromDsn($this->get(PubSubClientFactory::class), 'gps:', [
            'project_id' => 'emulator-project', // Project id is hardcoded when emulator is enabled
            'topic_name' => \getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS'),
            'subscription_name' => \getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS').'_subscription',
            'auto_setup' => true,
        ]);
        $topic = $client->getTopic();
        $this->enrichedProductsSubscription = $client->getSubscription();
        if (!$topic->exists()) {
            $topic->create();
        }
    }

    public function testItSendsOneMessageInQueue(): void
    {
        $this->cleanMessagesInQueue($this->enrichedProductsSubscription);

        $message = new class() implements Message {
            public function normalize(): array
            {
                return ['normalized'];
            }
        };

        $this->enrichedProductsQueue->publish($message);

        $messages = $this->enrichedProductsSubscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->enrichedProductsSubscription->acknowledgeBatch($messages);
        }
        self::assertCount(1, $messages);
        self::assertSame(['normalized'], \json_decode($messages[0]->data(), true));
    }

    public function testItSendsSeveralMessagesInQueue(): void
    {
        $this->cleanMessagesInQueue($this->enrichedProductsSubscription);

        $message1 = new class() implements Message {
            public function normalize(): array
            {
                return ['normalized1'];
            }
        };
        $message2 = new class() implements Message {
            public function normalize(): array
            {
                return ['normalized2'];
            }
        };
        $message3 = new class() implements Message {
            public function normalize(): array
            {
                return ['normalized3'];
            }
        };

        $this->enrichedProductsQueue->publishBatch([$message1, $message2, $message3]);

        $messages = $this->enrichedProductsSubscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->enrichedProductsSubscription->acknowledgeBatch($messages);
        }
        self::assertCount(3, $messages);
        self::assertSame(['normalized1'], \json_decode($messages[0]->data(), true));
        self::assertSame(['normalized2'], \json_decode($messages[1]->data(), true));
        self::assertSame(['normalized3'], \json_decode($messages[2]->data(), true));
    }

    private function cleanMessagesInQueue(Subscription $subscription): void
    {
        $messages = $subscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $subscription->acknowledgeBatch($messages);
        }
    }
}
