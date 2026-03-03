<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\PerformanceAnalytics\Domain\Message;
use Akeneo\Pim\Enrichment\Bundle\Normalizer\ProductModelsWereCreatedOrUpdatedNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelsWereCreatedOrUpdatedMessageIntegration extends EnrichmentProductModelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute('name');
        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);
        $this->createSimpleSelectAttributeWithOptions('size', ['38', '39', '40']);
        $this->createFamily('shoes', ['attributes' => ['name', 'color']]);
        $this->createFamilyVariant('shoes_color', 'shoes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->pubSubQueueStatus->flushJobQueue();
    }

    public function test_it_dispatches_message_when_product_model_is_created_or_updated(): void
    {
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());
        $code = 'product-model';
        $productModel = $this->createProductModel($code, 'shoes_color');

        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $productModelsWereCreatedOrUpdated = $this->get(ProductModelsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true, 512, JSON_THROW_ON_ERROR),
            ProductModelsWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(ProductModelsWereCreatedOrUpdated::class, $productModelsWereCreatedOrUpdated);
        self::assertCount(1, $productModelsWereCreatedOrUpdated->events);
        self::assertInstanceOf(ProductModelWasCreated::class, current($productModelsWereCreatedOrUpdated->events));

        $this->pubSubQueueStatus->flushJobQueue();
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        $this->updateProductModel($productModel, [
            'code' => 'product-model-1b',
        ]);

        /** @var Message $message */
        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        $message = current($messages);
        $productModelsWereCreatedOrUpdated = $this->get(ProductModelsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true, 512, JSON_THROW_ON_ERROR),
            ProductModelsWereCreatedOrUpdated::class
        );

        self::assertInstanceOf(ProductModelsWereCreatedOrUpdated::class, $productModelsWereCreatedOrUpdated);
        self::assertCount(1, $productModelsWereCreatedOrUpdated->events);
        self::assertInstanceOf(ProductModelWasUpdated::class, current($productModelsWereCreatedOrUpdated->events));
    }

    public function test_it_dispatches_message_when_product_models_are_created_or_updated(): void
    {
        $this->pubSubQueueStatus->flushJobQueue();
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        $codes = [
            'product-model-1',
            'product-model-2',
        ];

        $productModels = $this->createProductModels($codes, 'shoes_color');

        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $productModelsWereCreatedOrUpdated = $this->get(ProductModelsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true, 512, JSON_THROW_ON_ERROR),
            ProductModelsWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(ProductModelsWereCreatedOrUpdated::class, $productModelsWereCreatedOrUpdated);
        self::assertCount(2, $productModelsWereCreatedOrUpdated->events);
        self::assertInstanceOf(ProductModelWasCreated::class, current($productModelsWereCreatedOrUpdated->events));

        $this->pubSubQueueStatus->flushJobQueue();
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        $createdProductModel = $this->createProductModel('product-model-3', 'shoes_color', [], false);
        $updatedProductModel = $this->updateProductModel(current($productModels), [
            'code' => 'product-model-1b',
        ], false);

        $this->updateProductModels([$createdProductModel, $updatedProductModel]);

        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $productModelsWereCreatedOrUpdated = $this->get(ProductModelsWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true, 512, JSON_THROW_ON_ERROR),
            ProductModelsWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(ProductModelsWereCreatedOrUpdated::class, $productModelsWereCreatedOrUpdated);
        self::assertCount(2, $productModelsWereCreatedOrUpdated->events);

        $productModelWasCreatedList = \array_filter($productModelsWereCreatedOrUpdated->events, fn ($object) => $object instanceof ProductModelWasCreated);
        self::assertCount(1, $productModelWasCreatedList);

        $productModelWasUpdatedList = \array_filter($productModelsWereCreatedOrUpdated->events, fn ($object) => $object instanceof ProductModelWasUpdated);
        self::assertCount(1, $productModelWasUpdatedList);
    }
}
