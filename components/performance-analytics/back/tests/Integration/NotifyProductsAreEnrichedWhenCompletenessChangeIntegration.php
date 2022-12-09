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
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\CommandMessageBus;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

final class NotifyProductsAreEnrichedWhenCompletenessChangeIntegration extends TestCase
{
    private CommandMessageBus $messageBus;
    private ProductRepositoryInterface $productRepository;
    private Subscription $subscription;

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

        $productEnrichedAtCreation = $this->createProductEntity('product1', 'accessories', ['master'], [
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
        $productEnrichedAtCreation2 = $this->createProductEntity('product2', 'accessories', ['categoryA1'], [
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

        $productEnrichedAtUpdate = $this->createProductEntity('product_enriched_at_update', 'accessories', [], [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
            'a_simple_select' => [
                ['scope' => null, 'locale' => null, 'data' => 'optionA'],
            ],
        ]);

        $productNotEnrichedAtAll = $this->createProductEntity('product3', 'accessories', [], [
            'a_localized_and_scopable_text_area' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A textarea'],
            ],
        ]);

        $productWithoutFamily = $this->createProductEntity('product_without_family', null, [], [
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
        self::assertCount(2, $messages, 'There should be 2 messages after products creation');

        $message = $this->getDecodedMessageForProductChannelLocale($messages, $productEnrichedAtCreation->getUuid(), 'ecommerce', 'en_US');
        self::assertNotNull($message);
        unset($message['product_created_at']);
        unset($message['enriched_at']);
        self::assertSame([
            'product_uuid' => $productEnrichedAtCreation->getUuid()->toString(),
            'family_code' => 'accessories',
            'category_codes' => ['master'],
            'category_codes_with_ancestors' => ['master'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'author_id' => (string) $this->getUserId('admin'),
        ], $message);
        $message = $this->getDecodedMessageForProductChannelLocale($messages, $productEnrichedAtCreation2->getUuid(), 'ecommerce', 'en_US');
        self::assertNotNull($message);
        unset($message['product_created_at']);
        unset($message['enriched_at']);
        \sort($message['category_codes_with_ancestors']);
        self::assertSame([
            'product_uuid' => $productEnrichedAtCreation2->getUuid()->toString(),
            'family_code' => 'accessories',
            'category_codes' => ['categoryA1'],
            'category_codes_with_ancestors' => ['categoryA', 'categoryA1', 'master'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'author_id' => (string) $this->getUserId('admin'),
        ], $message);

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
        self::assertCount(3, $messages, 'There should be 3 messages after product update');

        $this->assertMessagesCountForProduct($productEnrichedAtUpdate->getUuid(), 3, $messages);
        $this->assertProductChannelLocaleIsInMessages($productEnrichedAtUpdate->getUuid(), 'ecommerce', 'en_US', $messages);
        $this->assertProductChannelLocaleIsInMessages($productEnrichedAtUpdate->getUuid(), 'tablet', 'en_US', $messages);
        $this->assertProductChannelLocaleIsInMessages($productEnrichedAtUpdate->getUuid(), 'tablet', 'fr_FR', $messages);
    }

    public function testItNotifiesVariantProductsAreEnrichedWhenCompletenessChange(): void
    {
        $this->createProductModel('root', null, 'familyVariantA1', ['categoryA1']);
        $this->createProductModel('sub', 'root', 'familyVariantA1', ['categoryB']);
        $product = $this->createOrUpdateProduct('test1', 'familyA', ['master_china'], [
            new ChangeParent('sub'),
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);

        $messages = $this->pullAndAckMessages();
        self::assertCount(2, $messages, 'There should be 2 messages after products creation');

        $message = $this->getDecodedMessageForProductChannelLocale($messages, $product->getUuid(), 'ecommerce_china', 'en_US');
        self::assertNotNull($message);
        unset($message['product_created_at']);
        unset($message['enriched_at']);
        \sort($message['category_codes']);
        \sort($message['category_codes_with_ancestors']);
        self::assertSame([
            'product_uuid' => $product->getUuid()->toString(),
            'family_code' => 'familyA',
            'category_codes' => ['categoryA1', 'categoryB', 'master_china'],
            'category_codes_with_ancestors' => ['categoryA', 'categoryA1', 'categoryB', 'master', 'master_china'],
            'channel_code' => 'ecommerce_china',
            'locale_code' => 'en_US',
            'author_id' => (string) $this->getUserId('admin'),
        ], $message);
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

    private function createProductEntity(
        string $identifier,
        ?string $familyCode,
        array $categoryCodes,
        array $values
    ): ProductInterface {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => $categoryCodes,
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

    private function createProductModel(string $code, ?string $parentCode, string $familyVariant, array $categoryCodes): void
    {
        $productModelBuilder = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->withCategories(...$categoryCodes)
        ;
        if (null !== $parentCode) {
            $productModelBuilder->withParent($parentCode);
        }

        $productModel = $productModelBuilder->build();
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function assertProductChannelLocaleIsInMessages(
        UuidInterface $productUuid,
        string $channelCode,
        string $localeCode,
        array $messages
    ): void {
        $found = false;
        foreach ($messages as $message) {
            $productInfo = \json_decode($message->data(), true);
            if ($productInfo['product_uuid'] === $productUuid->toString()
                && $productInfo['channel_code'] === $channelCode
                && $productInfo['locale_code'] === $localeCode) {
                $found = true;
            }
        }

        Assert::assertTrue($found, 'Product channel locale was not found');
    }

    private function getDecodedMessageForProductChannelLocale(
        array $messages,
        UuidInterface $productUuid,
        string $channelCode,
        string $localeCode
    ): ?array {
        foreach ($messages as $message) {
            $productInfo = \json_decode($message->data(), true);
            if ($productInfo['product_uuid'] === $productUuid->toString()
                && $productInfo['channel_code'] === $channelCode
                && $productInfo['locale_code'] === $localeCode) {
                return $productInfo;
            }
        }

        return null;
    }

    private function assertMessagesCountForProduct(
        UuidInterface $productUuid,
        int $expectedCount,
        array $messages
    ): void {
        $count = 0;
        foreach ($messages as $message) {
            $productInfo = \json_decode($message->data(), true);
            if ($productInfo['product_uuid'] === $productUuid->toString()) {
                ++$count;
            }
        }

        Assert::assertSame($expectedCount, $count, sprintf('There should be %d channel-locale for product %s', $expectedCount, $productUuid->toString()));
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
