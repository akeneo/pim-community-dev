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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PhpSpec\ObjectBehavior;

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
        $this->shouldThrow(\InvalidArgumentException::class)->during('get', [new CriterionCode('unknown_code')]);
    }

    public function it_filters_non_accepted_services(EvaluateCriterionInterface $evaluateCriterion)
    {
        $this->beConstructedWith([$evaluateCriterion->getWrappedObject(), new \stdClass()]);
        $evaluateCriterion->getCode()->willReturn(new CriterionCode('my_code'));
        $this->getCriterionCodes()->shouldBeLike([new CriterionCode('my_code')]);
        $this->get(new CriterionCode('my_code'))->shouldReturn($evaluateCriterion->getWrappedObject());
    }
}
