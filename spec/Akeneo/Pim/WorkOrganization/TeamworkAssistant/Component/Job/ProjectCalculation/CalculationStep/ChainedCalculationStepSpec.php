<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\ChainedCalculationStep;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

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
