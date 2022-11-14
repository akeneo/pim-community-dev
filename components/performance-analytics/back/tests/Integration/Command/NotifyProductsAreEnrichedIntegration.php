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
        $messages = $this->subscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->subscription->acknowledgeBatch($messages);
        }
    }

    public function testItNotifiesProductsAreEnriched(): void
    {
        $product1 = $this->createProduct('product1', 'familyA', ['categoryA', 'categoryC']);
        $product2 = $this->createProduct('product2', 'familyA1', ['categoryB']);

        $notifierProductsAreEnriched = new NotifyProductsAreEnriched([
            new ProductIsEnriched(
                $product1->getUuid()->toString(),
                'ecommerce',
                'en_US',
                new \DateTimeImmutable('2022-01-30')
            ),
            new ProductIsEnriched(
                $product1->getUuid()->toString(),
                'ecommerce',
                'fr_FR',
                new \DateTimeImmutable('2022-01-30')
            ),
            new ProductIsEnriched(
                $product2->getUuid()->toString(),
                'ecommerce',
                'fr_FR',
                new \DateTimeImmutable('2022-02-28')
            ),
        ]);

        $this->get(NotifyProductsAreEnrichedHandler::class)($notifierProductsAreEnriched);

        $messages = $this->subscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->subscription->acknowledgeBatch($messages);
        }
        self::assertCount(1, $messages);

        $data = \json_decode($messages[0]->data(), true);
        self::assertCount(2, $data);

        self::assertSame([
            'product_uuid' => $product1->getUuid()->toString(),
            'product_created_at' => $product1->getCreated()->format('c'),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA', 'categoryC'],
            'channels_locales' => [
                ['channel_code' => 'ecommerce', 'locale_code' => 'en_US'],
                ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR'],
            ],
            'enriched_at' => '2022-01-30T00:00:00+00:00',
        ], $data[0]);

        self::assertSame([
            'product_uuid' => $product2->getUuid()->toString(),
            'product_created_at' => $product2->getCreated()->format('c'),
            'family_code' => 'familyA1',
            'category_codes' => ['categoryB'],
            'channels_locales' => [
                ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR'],
            ],
            'enriched_at' => '2022-02-28T00:00:00+00:00',
        ], $data[1]);
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
}
