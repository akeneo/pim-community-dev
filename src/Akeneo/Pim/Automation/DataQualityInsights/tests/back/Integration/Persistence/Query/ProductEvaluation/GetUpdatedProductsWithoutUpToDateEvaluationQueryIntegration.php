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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQuery;
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

        $productIds = iterator_to_array($this->query->execute($today->modify('-1 DAY'), 5));

        $this->assertCount(1, $productIds);
        $this->assertCount(4, $productIds[0]);

        $this->assertExpectedProductId($expectedProductVariant1, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant2, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant3, $productIds[0]);
        $this->assertExpectedProductId($expectedProductVariant4, $productIds[0]);
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function createProductVariant(string $parentCode): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->withFamily('familyA')
            ->build();

        $this->get('pim_catalog.updater.product')->update($product, ['parent' => $parentCode]);
        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function updateProductEvaluationsAt(ProductId $productId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pimee_data_quality_insights_product_criteria_evaluation SET evaluated_at = :evaluated_at WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productId->toInt(),
        ]);
    }

    private function removeProductEvaluations(ProductId $productId): void
    {
        $query = <<<SQL
DELETE FROM pimee_data_quality_insights_product_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productId->toInt(),]);
    }

    private function updateProductAt(ProductId $productId, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $productId->toInt(),
        ]);
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
    }

    private function givenAProductWithoutAnyEvaluation(): ProductId
    {
        $productId = $this->createProduct();
        $this->removeProductEvaluations($productId);

        return $productId;
    }

    private function givenAnUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $updatedAt);
        $this->updateProductEvaluationsAt($productId, $updatedAt->modify('-1 SECOND'));

        return $productId;
    }

    private function givenAnUpdatedProductVariantWithAnOutdatedEvaluation(string $parentCode, \DateTimeImmutable $updatedAt): ProductId
    {
        $productId = $this->createProductVariant($parentCode);
        $this->updateProductAt($productId, $updatedAt);
        $this->updateProductEvaluationsAt($productId, $updatedAt->modify('-1 SECOND'));

        return $productId;
    }

    private function givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent(string $parentCode, \DateTimeImmutable $parentUpdatedAt): ProductId
    {
        $productId = $this->createProductVariant($parentCode);
        $this->updateProductAt($productId, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productId, $parentUpdatedAt->modify('-1 SECOND'));

        return $productId;
    }

    private function givenAProductVariantWithAnUpToDateEvaluation(string $parentCode, \DateTimeImmutable $parentUpdatedAt): ProductId
    {
        $productId = $this->createProductVariant($parentCode);
        $this->updateProductAt($productId, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productId, $parentUpdatedAt->modify('+1 SECOND'));

        return $productId;
    }

    private function givenAProductVariantWithoutEvaluation(string $parentCode): ProductId
    {
        $productId = $this->createProductVariant($parentCode);

        $this->removeProductEvaluations($productId);

        return $productId;
    }

    private function givenAnUpdatedProductWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 SECOND');
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $updatedAt);
        $this->updateProductEvaluationsAt($productId, $updatedAt->modify('+1 SECOND'));
    }

    private function givenAnOldUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $today)
    {
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $today->modify('-2 DAY'));
        $this->updateProductEvaluationsAt($productId, $today->modify('-3 DAY'));
    }

    private function givenAnOldUpdatedProductWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 DAY');
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $updatedAt);
        $this->updateProductEvaluationsAt($productId, $updatedAt->modify('+1 HOUR'));
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
