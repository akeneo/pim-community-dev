<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Model;

use PimEnterprise\Component\TeamworkAssistant\Model\ProjectCompleteness;
use PhpSpec\ObjectBehavior;

class ProjectCompletenessSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('40', '33', '64');
        $this->shouldHaveType(ProjectCompleteness::class);
    }

    function it_has_product_count_to_do()
    {
        $this->beConstructedWith('40', '33', '64');
        $this->getProductsCountTodo()->shouldReturn(40);
        $this->getProductsCountInProgress()->shouldReturn(33);
        $this->getProductsCountDone()->shouldReturn(64);
    }

    function it_calculates_ratio()
    {
        $this->beConstructedWith('40', '33', '64');
        $this->getRatioForTodo()->shouldReturn(29.20);
        $this->getRatioForInProgress()->shouldReturn(24.09);
        $this->getRatioForDone()->shouldReturn(46.72);
    }

    function it_knows_if_the_project_is_not_complete()
    {
        $this->beConstructedWith('40', '33', '64');
        $this->isComplete()->shouldReturn(false);
    }

    function it_knows_if_the_project_is_complete()
    {
        $this->beConstructedWith('0', '0', '64');
        $this->isComplete()->shouldReturn(true);
    }

    function it_does_not_divide_by_zero()
    {
        $this->beConstructedWith('0', '0', '0');
        $this->getRatioForTodo()->shouldReturn(0.00);
        $this->getRatioForInProgress()->shouldReturn(0.00);
        $this->getRatioForDone()->shouldReturn(0.00);
    }
}
