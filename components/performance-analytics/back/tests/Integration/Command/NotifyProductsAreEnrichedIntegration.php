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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\PubSub\Subscription;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

final class NotifyProductsAreEnrichedIntegration extends TestCase
{
    private Subscription $subscription;
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritDoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        putenv('PFID=fake_pfid');
        putenv('APP_TENANT_ID=fake_tenant_id');
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');

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
        $product1 = $this->createProduct('product1', 'familyA', ['categoryA', 'categoryC']);
        $product2 = $this->createProduct('product2', 'familyA1', ['categoryB']);

        $notifierProductsAreEnriched = new NotifyProductsAreEnriched([
            new ProductIsEnriched(
                $product1->getUuid(),
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('en_US'),
                new \DateTimeImmutable('2022-01-30')
            ),
            new ProductIsEnriched(
                $product1->getUuid(),
                ChannelCode::fromString('ecommerce'),
                LocaleCode::fromString('fr_FR'),
                new \DateTimeImmutable('2022-01-30')
            ),
            new ProductIsEnriched(
                $product2->getUuid(),
                ChannelCode::fromString('mobile'),
                LocaleCode::fromString('fr_FR'),
                new \DateTimeImmutable('2022-02-28')
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

        self::assertSame([
            'product_uuid' => $product1->getUuid()->toString(),
            'product_created_at' => $product1->getCreated()->format('c'),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA', 'categoryC'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'enriched_at' => '2022-01-30T00:00:00+00:00',
        ], \json_decode($messages[0]->data(), true));

        self::assertSame([
            'product_uuid' => $product1->getUuid()->toString(),
            'product_created_at' => $product1->getCreated()->format('c'),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA', 'categoryC'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'fr_FR',
            'enriched_at' => '2022-01-30T00:00:00+00:00',
        ], \json_decode($messages[1]->data(), true));

        self::assertSame([
            'product_uuid' => $product2->getUuid()->toString(),
            'product_created_at' => $product2->getCreated()->format('c'),
            'family_code' => 'familyA1',
            'category_codes' => ['categoryB'],
            'channel_code' => 'mobile',
            'locale_code' => 'fr_FR',
            'enriched_at' => '2022-02-28T00:00:00+00:00',
        ], \json_decode($messages[2]->data(), true));
    }

    private function createProduct(
        string $identifier,
        string $familyCode,
        array $categories
    ): ProductInterface {
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier($identifier), userIntents: [
            new SetFamily($familyCode),
            new SetCategories($categories),
        ]);
        $this->messageBus->dispatch($command);

        return $this->productRepository->findOneByIdentifier($identifier);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function pullAndAckMessages(): array
    {
        $messages = $this->subscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->subscription->acknowledgeBatch($messages);
        }

        return $messages;
    }
}
