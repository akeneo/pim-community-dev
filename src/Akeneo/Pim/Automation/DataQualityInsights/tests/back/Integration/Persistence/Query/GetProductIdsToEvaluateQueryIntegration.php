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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetProductIdsToEvaluateQuery;
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
        $this->assertEquals([], iterator_to_array($this->productQuery->execute(4, 2)));

        $product1Id = $this->createProduct('p1');
        $product2Id = $this->createProduct('p2');
        $product3Id = $this->createProduct('p3');
        $product4Id = $this->createProduct('p4');
        $criteria = $this->getCriteriaEvaluationsSample($product1Id, $product2Id, $product3Id, $product4Id);

        $this->productCriterionEvaluationRepository->create($criteria);

        $expectedProductIds = [
            [$product1Id->toInt(), $product2Id->toInt()],
            [$product3Id->toInt(), $product4Id->toInt()],
        ];

        $productIds = iterator_to_array($this->productQuery->execute(4, 2));

        $this->assertEqualsCanonicalizing($expectedProductIds, $productIds);
    }

    private function getCriteriaEvaluationsSample(ProductId $product1Id, ProductId $product2Id, ProductId $product3Id, ProductId $product4Id): CriterionEvaluationCollection
    {
        return (new CriterionEvaluationCollection)
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('9a7e76b6-220d-498d-aa97-3db425f2fa25'),
                new CriterionCode('completeness'),
                new ProductId(9999),
                new \DateTimeImmutable('2019-10-28 10:41:56.001'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
                new CriterionCode('completeness'),
                $product1Id,
                new \DateTimeImmutable('2019-10-28 10:41:56.123'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
                new CriterionCode('completion'),
                $product2Id,
                new \DateTimeImmutable('2019-10-28 10:41:57.987'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('dd292dbf-4c15-4b17-87ac-98997859d8af'),
                new CriterionCode('completion'),
                $product2Id,
                new \DateTimeImmutable('2019-10-28 10:41:56.987'),
                CriterionEvaluationStatus::done()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('8c94ed27-1394-4bce-8167-a81fe363b061'),
                new CriterionCode('completion'),
                $product3Id,
                new \DateTimeImmutable('2019-10-28 10:41:58.987'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('3e16311f-6cfc-47ec-a340-9628818dd3aa'),
                new CriterionCode('completion'),
                new ProductId(789),
                new \DateTimeImmutable('2019-10-28 10:41:59.123'),
                CriterionEvaluationStatus::done()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('46f6cfd0-ea65-4521-b572-629ca8057d2f'),
                new CriterionCode('completion'),
                $product4Id,
                new \DateTimeImmutable('2019-10-28 10:41:59.234'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('cde4714b-9826-4208-a4e9-434eff68c31e'),
                new CriterionCode('completion'),
                new ProductId(666),
                new \DateTimeImmutable('2019-10-28 10:41:59.456'),
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
}
