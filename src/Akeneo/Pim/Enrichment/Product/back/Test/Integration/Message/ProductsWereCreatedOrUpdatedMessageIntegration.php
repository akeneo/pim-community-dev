<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\back\Test\Integration\Message;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Normalizer\ProductsWereCreatedOrUpdatedNormalizer;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Google\Cloud\PubSub\Message;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereCreatedOrUpdatedMessageIntegration extends EnrichmentProductTestCase
{
    private PubSubQueueStatus $pubSubQueueStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pubSubQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.product_was_created_or_updated');
        $this->pubSubQueueStatus->flushJobQueue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->pubSubQueueStatus->flushJobQueue();
    }

    public function test_it_dispatches_message_when_product_is_created_or_updated(): void
    {
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());
        $this->createProduct('id1', []);
        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $productsWereCreatedOrUpdated = $this->get(ProductsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            ProductsWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(ProductsWereCreatedOrUpdated::class, $productsWereCreatedOrUpdated);
        self::assertCount(1, $productsWereCreatedOrUpdated->events);
        self::assertInstanceOf(ProductWasCreated::class, current($productsWereCreatedOrUpdated->events));
    }

    public function test_it_dispatches_message_when_products_are_created_or_updated(): void
    {
        $this->createProduct('id1', []);
        $this->pubSubQueueStatus->flushJobQueue();

        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        /** @var ProductInterface $product1 */
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        $product1->setEnabled(!$product1->isEnabled());
        self::assertCount(0, $this->get('pim_catalog.validator.product')->validate($product1));

        $product2 = $this->get('pim_catalog.builder.product')->createProduct('id2');
        self::assertCount(0, $this->get('pim_catalog.validator.product')->validate($product2));

        $this->get('pim_catalog.saver.product')->saveAll([$product1, $product2]);


        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $productsWereCreatedOrUpdated = $this->get(ProductsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            ProductsWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(ProductsWereCreatedOrUpdated::class, $productsWereCreatedOrUpdated);

        self::assertCount(2, $productsWereCreatedOrUpdated->events);

        $productWasCreatedList = \array_filter($productsWereCreatedOrUpdated->events, fn ($object) => $object instanceof ProductWasCreated);
        self::assertCount(1, $productWasCreatedList);

        $productWasUpdatedList = \array_filter($productsWereCreatedOrUpdated->events, fn ($object) => $object instanceof ProductWasUpdated);
        self::assertCount(1, $productWasUpdatedList);
    }
}
