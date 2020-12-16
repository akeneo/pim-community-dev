<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaEvaluationRegistrySpec extends ObjectBehavior
{
    public function it_returns_no_criterion_codes_if_no_services_are_injected()
    {
        $this->beConstructedWith([]);
        $this->getCriterionCodes()->shouldReturn([]);
    }

    public function it_throws_an_exception_if_an_evaluation_service_does_not_exist()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(CriterionNotFoundException::class)->during('get', [new CriterionCode('unknown_code')]);
    }

    public function it_filters_non_accepted_services(EvaluateCriterionInterface $evaluateCriterion)
    {
        $this->beConstructedWith([$evaluateCriterion->getWrappedObject(), new \stdClass()]);
        $evaluateCriterion->getCode()->willReturn(new CriterionCode('my_code'));
        $this->getCriterionCodes()->shouldBeLike([new CriterionCode('my_code')]);
        $this->get(new CriterionCode('my_code'))->shouldReturn($evaluateCriterion->getWrappedObject());
    }

    public function it_gives_the_coefficient_of_a_given_criterion(
        EvaluateCriterionInterface $evaluateCriterionA,
        EvaluateCriterionInterface $evaluateCriterionB
    ) {
        $this->beConstructedWith([$evaluateCriterionA->getWrappedObject(), $evaluateCriterionB->getWrappedObject()]);

        $evaluateCriterionA->getCode()->willReturn(new CriterionCode('criterion_A'));
        $evaluateCriterionB->getCode()->willReturn(new CriterionCode('criterion_B'));

        $evaluateCriterionA->getCoefficient()->willReturn(1);

        $this->getCriterionCoefficient(new CriterionCode('criterion_A'))->shouldReturn(1);
    }
}
