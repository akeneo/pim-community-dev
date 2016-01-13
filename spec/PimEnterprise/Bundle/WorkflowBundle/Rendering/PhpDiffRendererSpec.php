<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Rendering;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\DiffFactory;

class PhpDiffRendererSpec extends ObjectBehavior
{
    function let(
        \Diff_Renderer_Html_Array $baseRenderer,
        \Diff_Renderer_Html_Array $changedRenderer,
        DiffFactory $factory
    ) {
        $this->beConstructedWith($baseRenderer, $changedRenderer, $factory);
    }

    function it_is_a_renderer()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface');
    }

    function it_renders_original_diff_between_two_variables($baseRenderer, $factory, \Diff $diff)
    {
        $factory->create('foo', 'bar')->willReturn($diff);
        $diff->render($baseRenderer)->willReturn('3 letters');

        $this->renderOriginalDiff('foo', 'bar')->shouldReturn('3 letters');
    }

    function it_renders_new_diff_between_two_variables($changedRenderer, $factory, \Diff $diff)
    {
        $factory->create('foo', 'bar')->willReturn($diff);
        $diff->render($changedRenderer)->willReturn('3 letters');

        $this->renderNewDiff('foo', 'bar')->shouldReturn('3 letters');
    }
}
