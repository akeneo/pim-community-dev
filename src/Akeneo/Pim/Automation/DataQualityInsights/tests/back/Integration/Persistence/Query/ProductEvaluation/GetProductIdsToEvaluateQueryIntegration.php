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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductIdsToEvaluateQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class GetProductIdsToEvaluateQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var GetProductIdsToEvaluateQuery */
    private $productQuery;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->productQuery = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_ids_to_evaluate');
        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_returns_all_product_id_with_pending_criteria_and_ignores_unknown_products()
    {
        $this->givenAProductWithEvaluationDone();

        $this->assertEquals([], iterator_to_array($this->productQuery->execute(4, 2)), 'All products evaluations should be done');

        $product1Id = $this->createProduct('p1');
        $product2Id = $this->createProduct('p2');
        $product3Id = $this->createProduct('p3');
        $product4Id = $this->createProduct('p4');
        $criteria = $this->getPendingCriteriaEvaluationsSample($product1Id, $product2Id, $product3Id, $product4Id);

        $this->productCriterionEvaluationRepository->create($criteria);

        $expectedProductIds = [$product1Id->toInt(), $product2Id->toInt(), $product3Id->toInt(), $product4Id->toInt()];

        $productIds = iterator_to_array($this->productQuery->execute(4, 2));

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge($productIds[0], $productIds[1]));
    }

    private function getPendingCriteriaEvaluationsSample(ProductId $product1Id, ProductId $product2Id, ProductId $product3Id, ProductId $product4Id): Write\CriterionEvaluationCollection
    {
        return (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductId(9999),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $product1Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                $product1Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                $product2Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                $product3Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                $product4Id,
                CriterionEvaluationStatus::pending()
            ));
    }

    private function createProduct(string $identifier)
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->build();
        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenAProductWithEvaluationDone(): void
    {
        $productId = $this->createProduct('product_with_evaluations_done');
        $evaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection)->add($evaluationDone);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluationDone->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);
    }
}
