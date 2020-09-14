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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsToEvaluateQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class GetProductModelIdsToEvaluateQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

        /** @var GetProductModelIdsToEvaluateQuery */
    private $productModelQuery;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->productModelQuery = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_ids_to_evaluate');
        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
    }

    public function test_it_returns_all_product_id_with_pending_criteria_and_ignores_unknown_products()
    {
        $this->givenAProductModelWithEvaluationDone();

        $this->assertEquals([], iterator_to_array($this->productModelQuery->execute(4, 2)), 'All product models evaluations should be done');

        $product1Id = $this->createProductModel('p1');
        $product2Id = $this->createProductModel('p2');
        $product3Id = $this->createProductModel('p3');
        $product4Id = $this->createProductModel('p4');
        $criteria = $this->getCriteriaEvaluationsSample($product1Id, $product2Id, $product3Id, $product4Id);

        $this->productModelCriterionEvaluationRepository->create($criteria);

        $expectedProductIds = [$product1Id->toInt(), $product2Id->toInt(), $product3Id->toInt(), $product4Id->toInt()];

        $productIds = iterator_to_array($this->productModelQuery->execute(4, 2));

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge($productIds[0], $productIds[1]));
    }

    private function getCriteriaEvaluationsSample(ProductId $product1Id, ProductId $product2Id, ProductId $product3Id, ProductId $product4Id): CriterionEvaluationCollection
    {
        return (new CriterionEvaluationCollection)
            ->add(new CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductId(9999),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completeness'),
                $product1Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('spelling'),
                $product1Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completion'),
                $product2Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completion'),
                $product3Id,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completion'),
                $product4Id,
                CriterionEvaluationStatus::pending()
            ));
    }

    private function createProductModel(string $identifier)
    {
        $product = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($identifier)
            ->withFamilyVariant('familyVariantA1')
            ->build();
        $this->get('pim_catalog.saver.product_model')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function givenAProductModelWithEvaluationDone(): void
    {
        $productId = $this->createProductModel('product_model_with_evaluations_done');
        $evaluationDone = new CriterionEvaluation(
            new CriterionCode('completeness'),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new CriterionEvaluationCollection)->add($evaluationDone);
        $this->productModelCriterionEvaluationRepository->create($evaluations);

        $evaluationDone->end(new CriterionEvaluationResult());
        $this->productModelCriterionEvaluationRepository->update($evaluations);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
