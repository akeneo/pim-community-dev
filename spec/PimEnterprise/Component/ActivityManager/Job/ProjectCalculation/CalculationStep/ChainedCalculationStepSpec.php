<?php

namespace spec\Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\ChainedCalculationStep;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
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
