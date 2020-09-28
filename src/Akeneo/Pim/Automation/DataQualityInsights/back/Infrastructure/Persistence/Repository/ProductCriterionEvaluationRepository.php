<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Doctrine\DBAL\Connection;

final class ProductCriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
{
    private const DELETE_BATCH_SIZE = 1000;

    /** @var Connection */
    private $db;

    /** @var CriterionEvaluationRepository */
    private $repository;

    public function __construct(Connection $db, CriterionEvaluationRepository $repository)
    {
        $this->db = $db;
        $this->repository = $repository;
    }

    public function create(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $this->repository->createCriterionEvaluationsForProducts($criteriaEvaluations);
    }


    public function update(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $this->repository->updateCriterionEvaluationsForProducts($criteriaEvaluations);
    }

    public function deleteUnknownProductsEvaluations(): void
    {
        $query = <<<SQL
SELECT evaluation.product_id
FROM pim_data_quality_insights_product_criteria_evaluation AS evaluation
LEFT JOIN pim_catalog_product AS product ON(evaluation.product_id = product.id)
WHERE product.id IS NULL
SQL;

        $stmt = $this->db->executeQuery($query);

        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = $productId;

            if (count($productIds) >= self::DELETE_BATCH_SIZE) {
                $this->deleteByProductIds($productIds);
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            $this->deleteByProductIds($productIds);
        }
    }

    private function deleteByProductIds(array $productIds): void
    {
        $deleteQuery = <<<SQL
DELETE evaluation FROM pim_data_quality_insights_product_criteria_evaluation AS evaluation
WHERE evaluation.product_id IN (:productIds)
SQL;
        $this->db->executeQuery(
            $deleteQuery,
            ['productIds' => $productIds],
            ['productIds' => Connection::PARAM_INT_ARRAY]
        );
    }
}
