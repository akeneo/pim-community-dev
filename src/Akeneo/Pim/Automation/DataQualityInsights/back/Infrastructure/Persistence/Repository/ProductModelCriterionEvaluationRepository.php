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

final class ProductModelCriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
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
        $this->repository->createCriterionEvaluationsForProductModels($criteriaEvaluations);
    }

    public function update(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $this->repository->updateCriterionEvaluationsForProductModels($criteriaEvaluations);
    }

    public function deleteUnknownProductsEvaluations(): void
    {
        $query = <<<SQL
DELETE evaluation
FROM pimee_data_quality_insights_product_model_criteria_evaluation AS evaluation
LEFT JOIN pim_catalog_product_model AS product_model ON(evaluation.product_id = product_model.id)
WHERE product_model.id IS NULL
SQL;
        $this->db->executeQuery($query);
    }
}
