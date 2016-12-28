<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\LoggableStep;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
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

    function it_log_the_memory_usage(
        ProductInterface $product,
        ProjectInterface $project
    ) {
        $product->getId()->shouldBeCalled();
        $project->getCode()->shouldBeCalled();

        $this->execute($product, $project)->shouldReturn(null);
    }
}
