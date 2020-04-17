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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class CreateCriteriaEvaluations
{
    /** @var CriteriaEvaluationRegistry */
    private $criteriaEvaluationRegistry;

    /** @var CriterionEvaluationRepositoryInterface */
    private $criterionEvaluationRepository;

    public function __construct(
        CriteriaEvaluationRegistry $criteriaEvaluationRegistry,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository
    ) {
        $this->criteriaEvaluationRegistry = $criteriaEvaluationRegistry;
        $this->criterionEvaluationRepository = $criterionEvaluationRepository;
    }

    /**
     * @param ProductId[] $productIds
     */
    public function create(array $productIds): void
    {
        $criteria = new Write\CriterionEvaluationCollection();

        foreach ($productIds as $productId) {
            foreach ($this->criteriaEvaluationRegistry->getCriterionCodes() as $criterionCode) {
                $criteria->add(new Write\CriterionEvaluation(
                    $criterionCode,
                    $productId,
                    CriterionEvaluationStatus::pending()
                ));
            }
        }

        $this->criterionEvaluationRepository->create($criteria);
    }
}
