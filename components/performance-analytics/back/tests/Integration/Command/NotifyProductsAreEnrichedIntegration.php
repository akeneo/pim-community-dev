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

namespace Akeneo\Test\PerformanceAnalytics\Integration\Command;

use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnriched;
use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnrichedHandler;
use Akeneo\PerformanceAnalytics\Application\Command\ProductIsEnriched;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnrichedMessage;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Test\PerformanceAnalytics\Integration\PerformanceAnalyticsTestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;

final class NotifyProductsAreEnrichedIntegration extends PerformanceAnalyticsTestCase
{
    private Subscription $subscription;

    protected function setUp(): void
    {
        putenv('PFID=fake_pfid');
        putenv('APP_TENANT_ID=fake_tenant_id');
        parent::setUp();

        $client = Client::fromDsn($this->get(PubSubClientFactory::class), 'gps:', [
            'project_id' => 'emulator-project', // Project id is hardcoded when emulator is enabled
            'topic_name' => \getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS'),
            'subscription_name' => \getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS').'_subscription',
            'auto_setup' => true,
        ]);
        $topic = $client->getTopic();
        $this->subscription = $client->getSubscription();
        if (!$topic->exists()) {
            $topic->create();
        }

        // Empty the messages in the queue
        $this->pullAndAckMessages();
    }

    public function testItNotifiesProductsAreEnriched(): void
    {
        $this->createCategory(['code' => 'categoryA', 'parent' => 'master']);
        $this->createCategory(['code' => 'categoryB', 'parent' => 'master']);
        $this->createCategory(['code' => 'categoryC', 'parent' => 'master']);
        $this->createFamily('familyA', ['attributes' => ['sku']]);
        $this->createFamily('familyA1', ['attributes' => ['sku']]);
        $product1 = $this->createProduct('product1', [
            new SetFamily('familyA'),
            new SetCategories(['categoryA', 'categoryC']),
        ]);
        $product2 = $this->createProduct('product2', [
            new SetFamily('familyA1'),
            new SetCategories(['categoryB']),
        ]);

        $notifierProductsAreEnriched = new NotifyProductsAreEnriched([
            new ProductIsEnriched(
                $product1->getUuid(),
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('en_US'),
                new \DateTimeImmutable('2022-01-30'),
                '1'
            ),
            new ProductIsEnriched(
                $product1->getUuid(),
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                new \DateTimeImmutable('2022-01-30'),
                '1'
            ),
            new ProductIsEnriched(
                $product2->getUuid(),
                ChannelCode::fromString('mobile'),
                LocaleCode::fromString('fr_FR'),
                new \DateTimeImmutable('2022-02-28'),
                '1'
            ),
        ]);

        $this->pullAndAckMessages();
        $this->get(NotifyProductsAreEnrichedHandler::class)($notifierProductsAreEnriched);

        $messages = $this->pullAndAckMessages();
        self::assertCount(3, $messages, 'There should be 3 messages');

        $attributes = $messages[0]->attributes();
        self::assertSame(ProductWasEnrichedMessage::class, $attributes['class'] ?? null);
        self::assertSame('fake_pfid', $attributes['pfid'] ?? null);
        self::assertSame('fake_tenant_id', $attributes['tenant_id'] ?? null);

        $message1 = \json_decode($messages[0]->data(), true);
        \sort($message1['category_codes']);
        \sort($message1['category_codes_with_ancestors']);
        self::assertSame([
            'product_uuid' => $product1->getUuid()->toString(),
            'product_created_at' => $product1->getCreated()->format('c'),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA', 'categoryC'],
            'category_codes_with_ancestors' => ['categoryA', 'categoryC', 'master'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'enriched_at' => '2022-01-30T00:00:00+00:00',
            'author_id' => '1',
        ], $message1);

        $message2 = \json_decode($messages[1]->data(), true);
        \sort($message2['category_codes']);
        \sort($message2['category_codes_with_ancestors']);
        self::assertSame([
            'product_uuid' => $product1->getUuid()->toString(),
            'product_created_at' => $product1->getCreated()->format('c'),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA', 'categoryC'],
            'category_codes_with_ancestors' => ['categoryA', 'categoryC', 'master'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'fr_FR',
            'enriched_at' => '2022-01-30T00:00:00+00:00',
            'author_id' => '1',
        ], $message2);

        $message3 = \json_decode($messages[2]->data(), true);
        \sort($message3['category_codes']);
        \sort($message3['category_codes_with_ancestors']);
        self::assertSame([
            'product_uuid' => $product2->getUuid()->toString(),
            'product_created_at' => $product2->getCreated()->format('c'),
            'family_code' => 'familyA1',
            'category_codes' => ['categoryB'],
            'category_codes_with_ancestors' => ['categoryB', 'master'],
            'channel_code' => 'mobile',
            'locale_code' => 'fr_FR',
            'enriched_at' => '2022-02-28T00:00:00+00:00',
            'author_id' => '1',
        ], $message3);
    }

    /**
     * @return Message[]
     */
    private function pullAndAckMessages(): array
    {
        $allMessages = [];
        do {
            $messages = $this->subscription->pull(['returnImmediately' => true]);
            if ([] !== $messages) {
                $this->subscription->acknowledgeBatch($messages);
            }
            $allMessages = [...$allMessages, ...$messages];
        } while ([] !== $messages);

        return $allMessages;
    }
}
