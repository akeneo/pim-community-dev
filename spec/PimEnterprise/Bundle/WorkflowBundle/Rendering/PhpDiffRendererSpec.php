<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Rendering;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\DiffFactory;

class PhpDiffRendererSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_renderer()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface');
    }

    function it_renders_a_diff_between_two_variables($renderer, $factory, \Diff $diff)
    {
        $factory->create('foo', 'bar')->willReturn($diff);
        $diff->render($renderer)->willReturn('3 letters');

        $this->renderDiff('foo', 'bar')->shouldReturn('3 letters');
    }
}
