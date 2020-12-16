<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
    public function createAll(array $productIds): void
    {
        $this->create($this->criteriaEvaluationRegistry->getCriterionCodes(), $productIds);
    }

    /**
     * @param CriterionCode[] $criterionCodes
     * @param ProductId[] $productIds
     */
    public function create(array $criterionCodes, array $productIds): void
    {
        $criteria = new Write\CriterionEvaluationCollection();

        foreach ($productIds as $productId) {
            foreach ($criterionCodes as $criterionCode) {
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
