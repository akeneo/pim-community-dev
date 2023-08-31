<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductScoreOnProductCreateOrUpdateEndToEnd extends MessengerTestCase
{
    private const CONSUMER_NAME = 'dqi_product_score_compute';

    private PubSubQueueStatus $productScoreComputeOnUpsertQueueStatus;

    protected function setUp(): void
    {
        $this->productScoreComputeOnUpsertQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_product_score_compute_on_upsert');
        $this->pubSubQueueStatuses = [$this->productScoreComputeOnUpsertQueueStatus];

        parent::setUp();
    }

    public function test_it_computes_product_score_after_creation(): void
    {
        $uuid1 = Uuid::uuid4();
        $this->upsertProductWithUuid($uuid1);

        self::assertFalse($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));
        $this->launchConsumer(self::CONSUMER_NAME);
        self::assertTrue($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));
    }

    public function test_it_computes_product_score_after_update(): void
    {
        $uuid1 = Uuid::uuid4();
        $this->upsertProductWithUuid($uuid1);

        $this->productScoreComputeOnUpsertQueueStatus->flushJobQueue();
        $this->simulateOldProductScoreCompute();
        self::assertFalse($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));

        $this->upsertProductWithUuid($uuid1);
        $this->launchConsumer(self::CONSUMER_NAME);

        self::assertTrue($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));
    }

    public function test_it_computes_product_score_after_bulk_save(): void
    {
        $uuid1 = Uuid::uuid4();
        $this->upsertProductWithUuid($uuid1);

        $this->productScoreComputeOnUpsertQueueStatus->flushJobQueue();
        $this->simulateOldProductScoreCompute();
        self::assertFalse($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));

        /** @var ProductInterface $product1 */
        $product1 = $this->get('pim_catalog.repository.product')->findOneByUuid($uuid1);
        $product1->setEnabled(!$product1->isEnabled());
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
        self::assertCount(0, $this->get('pim_catalog.validator.product')->validate($product1));

        $product2 = $this->get('pim_catalog.builder.product')->createProduct('id2');
        $uuid2 = $product2->getUuid();
        self::assertCount(0, $this->get('pim_catalog.validator.product')->validate($product2));

        $this->get('pim_catalog.saver.product')->saveAll([$product1, $product2]);


        $this->launchConsumer(self::CONSUMER_NAME);

        self::assertTrue($this->isProductScoreComputed(ProductUuid::fromString($uuid1->toString())));
        self::assertTrue($this->isProductScoreComputed(ProductUuid::fromString($uuid2->toString())));
    }

    private function upsertProductWithUuid(UuidInterface $uuid): void
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->getUserId('admin'),
            \Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid::fromUuid($uuid),
            [new SetIdentifierValue('sku', (Uuid::uuid4())->toString())]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }
}
