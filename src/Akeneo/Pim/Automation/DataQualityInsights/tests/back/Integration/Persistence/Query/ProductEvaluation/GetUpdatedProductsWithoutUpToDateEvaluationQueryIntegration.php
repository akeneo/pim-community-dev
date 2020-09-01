<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\AssertionFailedError;
use Ramsey\Uuid\Uuid;

final class GetUpdatedProductsWithoutUpToDateEvaluationQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var GetUpdatedProductsWithoutUpToDateEvaluationQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(GetUpdatedProductsWithoutUpToDateEvaluationQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_all_updated_products_id_without_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($this->query->execute($today, 2)));

        $expectedProduct1 = $this->givenAProductWithoutAnyEvaluation();
        $expectedProduct2 = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $expectedProduct3 = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today->modify('-1 HOUR'));
        $this->givenAnUpdatedProductWithAnUpToDateEvaluation($today);
        $this->givenAnOldUpdatedProductWithAnOutdatedEvaluation($today);
        $this->givenAnOldUpdatedProductWithAnUpToDateEvaluation($today);
        $this->givenAnUpdatedProductWithPendingOutdatedEvaluations($today);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productIds = iterator_to_array($this->query->execute($today->modify('-1 DAY'), 2));

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertCount(1, $productIds[1]);
        $productIds = array_merge($productIds[0], $productIds[1]);

        $this->assertExpectedProductId($expectedProduct1, $productIds);
        $this->assertExpectedProductId($expectedProduct2, $productIds);
        $this->assertExpectedProductId($expectedProduct3, $productIds);
    }

    public function test_it_returns_updated_product_variants_without_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');

        $this->givenAProductModel('a_product_model_not_recently_updated', 'familyVariantA2', $today->modify('-1 DAY'));
        $expectedProductVariant1 = $this->givenAnUpdatedProductVariantWithAnOutdatedEvaluation('a_product_model_not_recently_updated', $today);
        $this->givenAProductVariantWithAnUpToDateEvaluation('a_product_model_not_recently_updated', $today);

        $this->givenAProductModel('a_recently_updated_product_model', 'familyVariantA2', $today);
        $expectedProductVariant2 = $this->givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent('a_recently_updated_product_model', $today);
        $expectedProductVariant3 = $this->givenAProductVariantWithoutEvaluation('a_recently_updated_product_model');
        $this->givenAProductVariantWithAnUpToDateEvaluation('a_recently_updated_product_model', $today);

        $this->givenAProductModel('a_product_model_with_two_variant_levels', 'familyVariantA1', $today);
        $this->givenASubProductModel('a_recently_updated_sub_product_model', 'familyVariantA1', 'a_product_model_with_two_variant_levels', $today);
        $expectedProductVariant4 = $this->givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent('a_recently_updated_sub_product_model', $today);
        $this->givenAProductVariantWithAnUpToDateEvaluation('a_recently_updated_sub_product_model', $today);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productIds = iterator_to_array($this->query->execute($today->modify('-1 DAY'), 5));

        $this->assertCount(1, $productIds);
        $this->assertCount(4, $productIds[0]);

        $this->assertExpectedProductId($expectedProductVariant1, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant2, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant3, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant4, $productIds[0]);
    }

    private function createProduct(): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function createProductVariant(string $parentCode): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->withFamily('familyA')
            ->build();

        $this->get('pim_catalog.updater.product')->update($product, ['parent' => $parentCode]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function updateProductEvaluationsAt(int $productId, \DateTimeImmutable $evaluatedAt, string $status = CriterionEvaluationStatus::DONE): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation 
SET evaluated_at = :evaluated_at, status = :status
WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productId,
            'status' => $status,
        ]);
    }

    private function removeProductEvaluations(int $productId): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productId]);
    }

    private function updateProductAt(ProductInterface $product, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $product->getId(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier($product->getIdentifier());
    }

    private function updateProductModelAt(string $productModelCode, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :updated WHERE code = :code;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'code' => $productModelCode,
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($productModelCode);
    }

    private function givenAProductWithoutAnyEvaluation(): ProductId
    {
        $product = $this->createProduct();
        $this->removeProductEvaluations($product->getId());

        return new ProductId((int) $product->getId());
    }

    private function givenAnUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);
        $this->updateProductEvaluationsAt($product->getId(), $updatedAt->modify('-1 SECOND'));

        return new ProductId($product->getId());
    }

    private function givenAnUpdatedProductWithPendingOutdatedEvaluations(\DateTimeImmutable $updatedAt): ProductId
    {
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);
        $this->updateProductEvaluationsAt($product->getId(), $updatedAt->modify('-1 SECOND'), CriterionEvaluationStatus::PENDING);

        return new ProductId($product->getId());
    }

    private function givenAnUpdatedProductVariantWithAnOutdatedEvaluation(string $parentCode, \DateTimeImmutable $updatedAt): ProductId
    {
        $productVariant = $this->createProductVariant($parentCode);
        $this->updateProductAt($productVariant, $updatedAt);
        $this->updateProductEvaluationsAt($productVariant->getId(), $updatedAt->modify('-1 SECOND'));

        return new ProductId($productVariant->getId());
    }

    private function givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent(string $parentCode, \DateTimeImmutable $parentUpdatedAt): ProductId
    {
        $productVariant = $this->createProductVariant($parentCode);
        $this->updateProductAt($productVariant, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productVariant->getId(), $parentUpdatedAt->modify('-1 SECOND'));

        return new ProductId($productVariant->getId());
    }

    private function givenAProductVariantWithAnUpToDateEvaluation(string $parentCode, \DateTimeImmutable $parentUpdatedAt): ProductId
    {
        $productVariant = $this->createProductVariant($parentCode);
        $this->updateProductAt($productVariant, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productVariant->getId(), $parentUpdatedAt->modify('+1 SECOND'));

        return new ProductId($productVariant->getId());
    }

    private function givenAProductVariantWithoutEvaluation(string $parentCode): ProductId
    {
        $productVariant = $this->createProductVariant($parentCode);

        $this->removeProductEvaluations($productVariant->getId());

        return new ProductId($productVariant->getId());
    }

    private function givenAnUpdatedProductWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 SECOND');
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);
        $this->updateProductEvaluationsAt($product->getId(), $updatedAt->modify('+1 SECOND'));
    }

    private function givenAnOldUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $today)
    {
        $product = $this->createProduct();
        $this->updateProductAt($product, $today->modify('-2 DAY'));
        $this->updateProductEvaluationsAt($product->getId(), $today->modify('-3 DAY'));
    }

    private function givenAnOldUpdatedProductWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 DAY');
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);
        $this->updateProductEvaluationsAt($product->getId(), $updatedAt->modify('+1 HOUR'));
    }

    private function givenAProductModel(string $productModelCode, string $familyVariant, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->updateProductModelAt($productModelCode, $updatedAt);
    }

    private function givenASubProductModel(string $productModelCode, string $familyVariant, string $parentCode, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->updateProductModelAt($productModelCode, $updatedAt);
    }

    private function assertExpectedProductId(ProductId $expectedProductId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            if ($productId->toInt() === $expectedProductId->toInt()) {
                return;
            }
        }

        throw new AssertionFailedError(sprintf('Expected product id %d not found', $expectedProductId->toInt()));
    }
}
