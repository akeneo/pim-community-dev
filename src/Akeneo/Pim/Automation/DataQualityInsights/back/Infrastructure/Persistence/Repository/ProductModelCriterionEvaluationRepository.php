<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelCriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
{
    private CriterionEvaluationRepository $repository;

    public function __construct(CriterionEvaluationRepository $repository)
    {
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
}
