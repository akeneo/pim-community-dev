<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCriteriaEvaluationsSpec extends ObjectBehavior
{
    public function it_creates_all_criteria(
        CriteriaByFeatureRegistry $criteriaRegistry,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository
    ) {
        $this->beConstructedWith($criteriaRegistry, $criterionEvaluationRepository);

        $productUuids = ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed']);

        $criteriaRegistry->getAllCriterionCodes()->willReturn([new CriterionCode('criterion1'), new CriterionCode('criterion2')]);

        $criterionEvaluationRepository->create(Argument::that(function (CriterionEvaluationCollection $collection) {
            return $collection->count() === 2;
        }))->shouldBeCalled();

        $this->createAll($productUuids);
    }
}
