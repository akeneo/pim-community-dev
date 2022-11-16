<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\PerformanceAnalytics\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Ramsey\Uuid\UuidInterface;

final class NotifyProductsAreEnrichedWhenCompletenessChangeIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        // @todo use a catalog specific to performance-analytics (JEL-71)
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->set('akeneo.performance_analytics.notify_enriched_products.feature', new EnvVarFeatureFlag(true));
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');

        $this->subscription = Client::fromDsn($this->get(PubSubClientFactory::class), 'gps:', [
            'project_id' => 'emulator-project',
            'topic_name' => getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS'),
            'subscription_name' => getenv('PUBSUB_TOPIC_PERFORMANCE_ANALYTICS').'_subscription',
            'auto_setup' => true,
        ])->getSubscription();

        // Empty the messages in the queue
        $this->pullAndAckMessages();
    }

    public function testItNotifiesProductsAreEnrichedWhenCompletenessChange(): void
    {
        $this->createFamily('accessories', [
            'attributes' => ['sku', 'a_localized_and_scopable_text_area', 'a_simple_select', 'a_yes_no'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'a_localized_and_scopable_text_area', 'a_simple_select', 'a_yes_no'],
                'ecommerce_china' => ['sku', 'a_localized_and_scopable_text_area', 'a_simple_select', 'a_yes_no'],
                'tablet' => ['sku', 'a_localized_and_scopable_text_area', 'a_simple_select', 'a_yes_no'],
            ],
        ]);

        $productEnrichedAtCreation = $this->createProductEntity('product1', 'accessories', [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
            'a_simple_select' => [
                ['scope' => null, 'locale' => null, 'data' => 'optionA'],
            ],
            'a_yes_no' => [
                ['scope' => null, 'locale' => null, 'data' => true],
            ],
        ]);
        $productEnrichedAtCreation2 = $this->createProductEntity('product2', 'accessories', [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
            'a_simple_select' => [
                ['scope' => null, 'locale' => null, 'data' => 'optionA'],
            ],
            'a_yes_no' => [
                ['scope' => null, 'locale' => null, 'data' => true],
            ],
        ]);

        $productEnrichedAtUpdate = $this->createProductEntity('product_enriched_at_update', 'accessories', [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
            'a_simple_select' => [
                ['scope' => null, 'locale' => null, 'data' => 'optionA'],
            ],
        ]);

        $productNotEnrichedAtAll = $this->createProductEntity('product3', 'accessories', [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
        ]);

        $productWithoutFamily = $this->createProductEntity('product_without_family', null, [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
            'a_simple_select' => [
                ['scope' => null, 'locale' => null, 'data' => 'optionA'],
            ],
            'a_yes_no' => [
                ['scope' => null, 'locale' => null, 'data' => true],
            ],
        ]);

        $this->get('pim_catalog.saver.product')->saveAll([
            $productEnrichedAtCreation,
            $productEnrichedAtCreation2,
            $productEnrichedAtUpdate,
            $productNotEnrichedAtAll,
            $productWithoutFamily,
        ]);

        // Product not enriched at all
        $this->createOrUpdateProduct(
            'product3',
            'accessories',
            ['categoryB'],
            [
                new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'A textarea'),
            ]
        );

        $messages = $this->pullAndAckMessages();
        self::assertCount(1, $messages, 'There should be 1 message');

        $data = \json_decode($messages[0]->data(), true);
        self::assertCount(2, $data, 'There should be 2 products in the message');

        $this->assertChannelLocaleCountForProduct($productEnrichedAtCreation->getUuid(), 1, $data);
        $this->assertProductChannelLocaleIsInMessage($productEnrichedAtCreation->getUuid(), 'ecommerce', 'en_US', $data);

        $productEnrichedAtUpdate = $this->createOrUpdateProduct(
            'product_enriched_at_update',
            'accessories',
            ['categoryB'],
            [
                new SetBooleanValue('a_yes_no', null, null, false),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'A textarea'),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'A textarea'),
            ]
        );

        $messages = $this->pullAndAckMessages();
        self::assertCount(1, $messages, 'There should be 1 message');

        $data = \json_decode($messages[0]->data(), true);
        self::assertCount(1, $data, 'There should be 1 product in the message');

        $this->assertChannelLocaleCountForProduct($productEnrichedAtUpdate->getUuid(), 3, $data);
        $this->assertProductChannelLocaleIsInMessage($productEnrichedAtUpdate->getUuid(), 'ecommerce', 'en_US', $data);
        $this->assertProductChannelLocaleIsInMessage($productEnrichedAtUpdate->getUuid(), 'tablet', 'en_US', $data);
        $this->assertProductChannelLocaleIsInMessage($productEnrichedAtUpdate->getUuid(), 'tablet', 'fr_FR', $data);
    }

    private function pullAndAckMessages(): array
    {
        $messages = $this->subscription->pull(['returnImmediately' => true]);
        if ([] !== $messages) {
            $this->subscription->acknowledgeBatch($messages);
        }

        return $messages;
    }

    private function createProductEntity(string $identifier, ?string $familyCode, array $values): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, [
            'values' => $values,
        ]);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, (string) $violations);

        return $product;
    }

    private function createOrUpdateProduct(
        string $identifier,
        string $familyCode,
        array $categories,
        array $valueUserIntents
    ): ProductInterface {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product afterwards
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('admin'),
            ProductIdentifier::fromIdentifier($identifier),
            [
                new SetFamily($familyCode),
                new SetCategories($categories),
                ...$valueUserIntents,
            ]
        );
        $this->messageBus->dispatch($command);

        return $this->productRepository->findOneByIdentifier($identifier);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function assertProductChannelLocaleIsInMessage(
        UuidInterface $productUuid,
        string $channelCode,
        string $localeCode,
        array $message
    ): void {
        $found = false;
        foreach ($message as $productInfo) {
            if ($productInfo['product_uuid'] === $productUuid->toString()) {
                foreach ($productInfo['channels_locales'] as $channelLocale) {
                    if ($channelLocale['channel_code'] === $channelCode && $channelLocale['locale_code'] === $localeCode) {
                        $found = true;
                    }
                }
            }
        }

        Assert::assertTrue($found, 'Product channel locale was not found');
    }

    private function assertChannelLocaleCountForProduct(
        UuidInterface $productUuid,
        int $channelLocaleCount,
        array $message
    ): void {
        foreach ($message as $productInfo) {
            if ($productInfo['product_uuid'] === $productUuid->toString()) {
                Assert::assertCount($channelLocaleCount, $productInfo['channels_locales']);

                return;
            }
        }

        throw new ExpectationFailedException('Product not found in the message');
    }

    private function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = \array_merge(['code' => $code], $data);

        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);
        $violations = $this->get('validator')->validate($family);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }
}
