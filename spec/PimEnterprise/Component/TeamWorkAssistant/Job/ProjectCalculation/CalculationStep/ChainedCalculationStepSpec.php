<?php

namespace spec\PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep;

use PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation\CalculationStep\ChainedCalculationStep;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class ChainedCalculationStepSpec extends ObjectBehavior
{
    function let(
        CalculationStepInterface $userGroupCalculationStep,
        CalculationStepInterface $otherCalculationStep
    ) {
        $this->beConstructedWith([$userGroupCalculationStep, $otherCalculationStep]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChainedCalculationStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_executes_all_calculation_step(
        $userGroupCalculationStep,
        $otherCalculationStep,
        ProductInterface $product,
        ProjectInterface $project
    ) {
        $userGroupCalculationStep->execute($product, $project)->shouldBeCalled();
        $otherCalculationStep->execute($product, $project)->shouldBeCalled();

        $this->execute($product, $project);
    }
}
