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
        return $this->catalog->useMinimalCatalog();
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

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function updateProductEvaluationsAt(ProductId $productId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pimee_data_quality_insights_criteria_evaluation SET created_at = :created_at WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'created_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productId->toInt(),
        ]);
    }

    private function removeProductEvaluations(ProductId $productId): void
    {
        $query = <<<SQL
DELETE FROM pimee_data_quality_insights_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productId->toInt(),]);
    }

    private function updateProductAt(ProductId $productId, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated_at WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $productId->toInt(),
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
