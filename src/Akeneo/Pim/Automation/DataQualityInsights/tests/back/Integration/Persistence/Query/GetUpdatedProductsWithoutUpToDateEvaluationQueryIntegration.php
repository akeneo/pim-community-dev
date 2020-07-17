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

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetUpdatedProductsWithoutUpToDateEvaluationQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\CriterionEvaluationRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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

    /** @var CriterionEvaluationRepositoryInterface */
    private $criterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(GetUpdatedProductsWithoutUpToDateEvaluationQuery::class);
        $this->criterionEvaluationRepository = $this->get(CriterionEvaluationRepository::class);
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
        $this->givenAnUpdatedProductModel($today);
        $this->givenAnUpdatedProductVariant($today);

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

    private function createProduct(): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function updateProductEvaluationsAt(int $productId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pimee_data_quality_insights_criteria_evaluation SET created_at = :created_at WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'created_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productId,
        ]);
    }

    private function removeProductEvaluations(int $productId): void
    {
        $query = <<<SQL
DELETE FROM pimee_data_quality_insights_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productId,]);
    }

    private function updateProductAt(ProductInterface $product, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated_at WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $product->getId(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier($product->getIdentifier());
    }

    private function updateProductModelAt(ProductModelInterface $productModel, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :updatedAt WHERE id = :productModelId;
SQL;

        $this->db->executeQuery($query, [
            'updatedAt' => $updatedAt->format('Y-m-d H:i:s'),
            'productModelId' => $productModel->getId(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($productModel->getCode());
    }

    private function givenAProductWithoutAnyEvaluation(): ProductId
    {
        $product = $this->createProduct();
        $this->removeProductEvaluations($product->getId());

        return new ProductId(intval($product->getId()));
    }

    private function givenAnUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);
        $this->updateProductEvaluationsAt($product->getId(), $updatedAt->modify('-1 SECOND'));

        return new ProductId(intval($product->getId()));
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

    private function assertExpectedProductId(ProductId $expectedProductId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            if ($productId->toInt() === $expectedProductId->toInt()) {
                return;
            }
        }

        throw new AssertionFailedError(sprintf('Expected product id %d not found', $expectedProductId->toInt()));
    }

    private function givenAnUpdatedProductModel(\DateTimeImmutable $today): void
    {
        $productModel = $this->createProductModel('an_updated_product_model');

        $this->updateProductModelAt($productModel, $today);
    }

    private function givenAnUpdatedProductVariant(\DateTimeImmutable $today): void
    {
        $this->createProductModel('a_product_model_parent');
        $productVariant = $this->createProduct();

        $this->get('pim_catalog.updater.product')->update($productVariant, [
            'family' => 'familyA',
            'parent' => 'a_product_model_parent'
        ]);
        $this->get('pim_catalog.saver.product')->save($productVariant);

        $this->updateProductAt($productVariant, $today);
    }

    private function createProductModel(string $code): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }
}
