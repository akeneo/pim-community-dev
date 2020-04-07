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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Doctrine\DBAL\Connection;

final class ProductCriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
{
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

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        $query = <<<SQL
DELETE old_evaluations
FROM pimee_data_quality_insights_criteria_evaluation AS old_evaluations
INNER JOIN pimee_data_quality_insights_criteria_evaluation AS younger_evaluations
    ON younger_evaluations.product_id = old_evaluations.product_id
    AND younger_evaluations.criterion_code = old_evaluations.criterion_code
    AND younger_evaluations.created_at > old_evaluations.created_at
WHERE old_evaluations.created_at < :purge_date
SQL;

        $this->db->executeQuery(
            $query,
            ['purge_date' => $date->format('Y-m-d 00:00:00')]
        );
    }

    public function deleteUnknownProductsPendingEvaluations(): void
    {
        $query = <<<SQL
DELETE evaluation
FROM pimee_data_quality_insights_criteria_evaluation AS evaluation
LEFT JOIN pim_catalog_product AS product ON(evaluation.product_id = product.id)
WHERE evaluation.status = :status
AND product.id IS NULL
SQL;
        $this->db->executeQuery($query, [
            'status' => CriterionEvaluationStatus::PENDING,
        ]);
    }
}
