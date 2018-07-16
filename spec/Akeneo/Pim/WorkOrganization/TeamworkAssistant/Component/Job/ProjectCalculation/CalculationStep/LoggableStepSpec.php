<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\LoggableStep;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Prophecy\Argument;

class LoggableStepSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/tmp/example.log');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoggableStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_logs_the_memory_usage(
        ProductInterface $product,
        ProjectInterface $project
    ) {
        $product->getId()->shouldBeCalled();
        $project->getCode()->shouldBeCalled();

        $this->execute($product, $project)->shouldReturn(null);
    }
}
