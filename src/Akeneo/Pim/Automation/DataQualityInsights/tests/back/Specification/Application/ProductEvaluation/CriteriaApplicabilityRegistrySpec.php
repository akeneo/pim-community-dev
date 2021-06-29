<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionApplicabilityInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaApplicabilityRegistrySpec extends ObjectBehavior
{
    public function it_returns_no_criterion_codes_if_no_services_are_injected()
    {
        $this->beConstructedWith([]);
        $this->getCriterionCodes()->shouldReturn([]);
    }

    public function it_returns_null_if_an_applicability_service_does_not_exist(
        EvaluateCriterionApplicabilityInterface $evaluateCriterionApplicability
    ) {
        $this->beConstructedWith([$evaluateCriterionApplicability->getWrappedObject()]);
        $evaluateCriterionApplicability->getCode()->willReturn(new CriterionCode('my_code'));

        $this->get(new CriterionCode('unknown_code'))->shouldReturn(null);
    }

    public function it_filters_non_accepted_services(EvaluateCriterionApplicabilityInterface $evaluateCriterionApplicability)
    {
        $this->beConstructedWith([$evaluateCriterionApplicability->getWrappedObject(), new \stdClass()]);
        $evaluateCriterionApplicability->getCode()->willReturn(new CriterionCode('my_code'));

        $this->getCriterionCodes()->shouldBeLike([new CriterionCode('my_code')]);
        $this->get(new CriterionCode('my_code'))->shouldReturn($evaluateCriterionApplicability->getWrappedObject());
    }
}
