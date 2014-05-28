<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class TextPresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_text_key()
    {
        $this->supportsChange(['text' => 'foo'])->shouldBe(true);
    }

    function it_presents_text_change_using_the_injected_renderer(
        $renderer,
        $factory,
        \Diff $diff
    ) {
        $factory->create(['bar'], ['foo'])->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between bar and foo');

        $this->present('bar', ['text' => 'foo'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_before_rendering_diff(
        $renderer,
        $factory,
        \Diff $diff
    ) {
        $factory->create(['<p>foo</p>', '<p>bar</p>'], ['<p>foo</p>'])->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between bar and foo');

        $this->present('<p>foo</p> <p>bar</p>', ['text' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_without_space_before_rendering_diff(
        $renderer,
        $factory,
        \Diff $diff
    ) {
        $factory->create(['<p>foo</p>', '<p>bar</p>'], ['<p>foo</p>'])->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between bar and foo');

        $this->present('<p>foo</p><p>bar</p>', ['text' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }
}
