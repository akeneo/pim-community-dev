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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionApplicabilityInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PhpSpec\ObjectBehavior;

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
