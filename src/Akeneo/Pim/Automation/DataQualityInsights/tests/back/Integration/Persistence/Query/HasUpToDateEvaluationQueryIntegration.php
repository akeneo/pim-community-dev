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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\HasUpToDateEvaluationQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class HasUpToDateEvaluationQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var HasUpToDateEvaluationQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(HasUpToDateEvaluationQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_true_if_a_product_has_an_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId = $this->givenAProductWithAnUpToDateEvaluation($today);
        $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);

        $productHasUpToDateEvaluation = $this->query->forProductId($productId);
        $this->assertTrue($productHasUpToDateEvaluation);
    }

    public function test_it_returns_false_if_a_product_has_outdated_evaluations()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $this->givenAProductWithAnUpToDateEvaluation($today);


        $productHasUpToDateEvaluation = $this->query->forProductId($productId);
        $this->assertFalse($productHasUpToDateEvaluation);
    }

    public function test_it_returns_the_ids_of_the_products_that_have_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $expectedProductIdA = $this->givenAProductWithAnUpToDateEvaluation($today);
        $expectedProductIdB = $this->givenAProductWithAnUpToDateEvaluation($today);
        $outdatedProductId = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $this->givenAProductWithAnUpToDateEvaluation($today);

        $productIdsWithUpToDateEvaluation = $this->query->forProductIds([$outdatedProductId, $expectedProductIdA, $expectedProductIdB]);
        $this->assertEquals([$expectedProductIdA, $expectedProductIdB], $productIdsWithUpToDateEvaluation);
    }

    public function test_it_returns_an_empty_array_if_no_product_has_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $outdatedProductId = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);

        $this->assertSame([], $this->query->forProductIds([$outdatedProductId]));
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function givenAProductWithAnUpToDateEvaluation(\DateTimeImmutable $today): ProductId
    {
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $today);
        $this->updateProductEvaluationsAt($productId, $today);

        return $productId;
    }

    private function givenAnUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $productId = $this->createProduct();
        $this->updateProductAt($productId, $updatedAt);
        $this->updateProductEvaluationsAt($productId, $updatedAt->modify('-1 SECOND'));

        return $productId;
    }

    private function updateProductAt(ProductId $productId, \DateTimeImmutable $updatedAt): void
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated_at WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $productId->toInt(),
        ]);
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
}
