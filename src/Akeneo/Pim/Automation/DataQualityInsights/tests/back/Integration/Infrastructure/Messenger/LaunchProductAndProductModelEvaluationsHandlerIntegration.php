<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandlerIntegration extends DataQualityInsightsTestCase
{
    private Connection $dbConnection;
    private Clock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->clock = $this->get(SystemClock::class);

        $this->createAttribute('name');
        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);
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

        $this->deleteAllProductModelCriterionEvaluations();
    }

    public function test_it_launches_product_and_product_model_evaluations(): void
    {
        $productToEvaluate1 = $this->createProduct('product_to_evaluate_1', ['family' => 'shoes']);
        $productToEvaluate2 = $this->createProduct('product_to_evaluate_2', ['family' => 'shoes']);
        $whateverProduct = $this->createProduct('whatever_product', ['family' => 'shoes']);

        $productToEvaluateUuid1 = ProductUuid::fromUuid($productToEvaluate1->getUuid());
        $productToEvaluateUuid2 = ProductUuid::fromUuid($productToEvaluate2->getUuid());
        $whateverProductUuid = ProductUuid::fromUuid($whateverProduct->getUuid());

        $productModelToEvaluate1 = $this->createProductModel('product_model_to_evaluate_1', 'shoes_color');
        $productModelToEvaluate2 = $this->createProductModel('product_model_to_evaluate_2', 'shoes_color');
        $whateverProductModel = $this->createProductModel('whatever_product_model', 'shoes_color');

        $productModelToEvaluateId1 = new ProductModelId($productModelToEvaluate1->getId());
        $productModelToEvaluateId2 = new ProductModelId($productModelToEvaluate2->getId());
        $whateverProductModelId = new ProductModelId($whateverProductModel->getId());

        $this->assertProductsAreNotEvaluated(ProductUuidCollection::fromProductUuids([$productToEvaluateUuid1, $productToEvaluateUuid2, $whateverProductUuid]));
        $this->assertProductModelsAreNotEvaluated(ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId1, $productModelToEvaluateId2, $whateverProductModelId]));

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            $this->clock->fromString('2023-03-16 14:46:32'),
            ProductUuidCollection::fromProductUuids([$productToEvaluateUuid1, $productToEvaluateUuid2]),
            ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId1, $productModelToEvaluateId2]),
            []
        );

        ($this->get(LaunchProductAndProductModelEvaluationsHandler::class))($message);

        $this->assertProductsAreEvaluated($message->productUuids);
        $this->assertProductModelsAreEvaluated($message->productModelIds);
    }

    private function assertProductsAreNotEvaluated(ProductUuidCollection $productUuids): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid IN (:product_uuids) AND evaluated_at IS NOT NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => $productUuids->toArrayBytes()],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some products are evaluated');
    }

    private function assertProductModelsAreNotEvaluated(ProductModelIdCollection $productModelIds): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) AND evaluated_at IS NOT NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_model_ids' => $productModelIds->toArrayString()],
            ['product_model_ids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some product models are evaluated');
    }

    private function assertProductsAreEvaluated(ProductUuidCollection $productUuids): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid IN (:product_uuids) AND evaluated_at IS NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => $productUuids->toArrayBytes()],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some products are not fully evaluated');
    }

    private function assertProductModelsAreEvaluated(ProductModelIdCollection $productModelIds): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) AND evaluated_at IS NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_model_ids' => $productModelIds->toArrayString()],
            ['product_model_ids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some product models are not fully evaluated');
    }
}
