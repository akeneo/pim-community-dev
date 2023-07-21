<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\EndToEnd;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd\MessengerTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Webmozart\Assert\Assert;

final class ComputeProductModelScoreOnProductCreateOrUpdateEndToEnd extends MessengerTestCase
{
    private PubSubQueueStatus $productModelScoreComputeOnUpsertQueueStatus;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->createAttribute('name');
        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);
        $this->createSimpleSelectAttributeWithOptions('size', ['38', '39', '40']);
        $this->createFamily('shoes', ['attributes' => ['sku', 'name', 'color']]);
        $this->createFamilyVariant('shoes_color', 'shoes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
            ],
        ]);

        $this->productModelScoreComputeOnUpsertQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_product_model_score_compute_on_upsert');

        // Be sure the subscription is created before any tests
        $subscription = $this->productModelScoreComputeOnUpsertQueueStatus->getSubscription();
        if (!$subscription->exists()) {
            $subscription->create();
        }

        $this->productModelScoreComputeOnUpsertQueueStatus->flushJobQueue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->productModelScoreComputeOnUpsertQueueStatus->flushJobQueue();
    }

    public function test_it_computes_product_model_score_after_creation(): void
    {
        $code = 'product-model-1';
        $productModel = $this->createProductModel($code, 'shoes_color');
        $productModelId = new ProductModelId($productModel->getId());

        self::assertFalse($this->isProductModelScoreComputed($productModelId));
        $this->launchConsumer('dqi_product_model_score_compute');
        self::assertTrue($this->isProductModelScoreComputed($productModelId));
    }

    public function test_it_computes_product_model_score_after_update(): void
    {
        $code = 'product-model-1';
        $productModel = $this->createProductModel($code, 'shoes_color');
        $productModelId = new ProductModelId($productModel->getId());

        $this->launchConsumer('dqi_product_model_score_compute');
        $this->productModelScoreComputeOnUpsertQueueStatus->flushJobQueue();

        $this->updateProductModel($productModel, [
            'code' => 'product-model-1b',
        ]);

        $this->simulateOldProductModelScoreCompute();
        $this->launchConsumer('dqi_product_model_score_compute');
        self::assertTrue($this->isProductModelScoreComputed($productModelId));
    }

    public function test_it_computes_product_model_score_after_bulk_save(): void
    {
        $codes = [
          'product-model-1',
          'product-model-2',
        ];

        $productModels = $this->createProductModels($codes, 'shoes_color');

        foreach ($productModels as $productModel) {
            $productModelId = new ProductModelId($productModel->getId());
            self::assertFalse($this->isProductModelScoreComputed($productModelId));
        }

        $this->launchConsumer('dqi_product_model_score_compute');

        foreach ($productModels as $productModel) {
            $productModelId = new ProductModelId($productModel->getId());
            self::assertTrue($this->isProductModelScoreComputed($productModelId));
        }
    }
}
