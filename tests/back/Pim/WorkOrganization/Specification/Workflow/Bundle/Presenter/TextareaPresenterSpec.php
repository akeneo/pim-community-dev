<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class TextareaPresenterSpec extends ObjectBehavior
{
    function let(RendererInterface $renderer)
    {
        $this->setRenderer($renderer);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_change_if_it_has_a_textarea_key()
    {
        $this->supports('pim_catalog_textarea')->shouldBe(true);
    }

    function it_presents_text_change_using_the_injected_renderer(RendererInterface $renderer)
    {
        $renderer
            ->renderDiff(['bar'], ['foo'])
            ->shouldBeCalled()
            ->willReturn('diff between bar and foo');

        $this->present('bar', ['data' => 'foo'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_before_rendering_diff(RendererInterface $renderer)
    {
        $renderer
            ->renderDiff(['<p>foo</p>','<p>bar</p>'],['<p>foo</p>'])
            ->shouldBeCalled()
            ->willReturn('diff between bar and foo');

        $this->present('<p>foo</p><p>bar</p>', ['data' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_every_base_html_tag_before_rendering_diff(RendererInterface $renderer)
    {
        $formerHtml = <<<HTML
<p>foo<br/></p><ul><li>1</li></ul><ol style="text-align: center"><li>other</li></ol><a href="">Test</a>
HTML;
        $newHtml = <<<HTML
<p>bar<br/></p><ul><li>2</li></ul><ol></ol><a href="https://akeneo.com">Test</a><span>new text</span>
HTML;

        $renderer
            ->renderDiff(
                [
                    '<p>foo<br/></p>',
                    '<ul><li>1</li></ul>',
                    '<ol style="text-align: center"><li>other</li></ol>',
                    '<a href="">Test</a>',
                ],
                [
                    '<p>bar<br/></p>',
                    '<ul><li>2</li></ul>',
                    '<ol></ol>',
                    '<a href="https://akeneo.com">Test</a>',
                    '<span>new text</span>',
                ]
            )->shouldBeCalled()
             ->willReturn('diff between foo and bar');

        $this->present($formerHtml, ['data' => $newHtml])->shouldReturn('diff between foo and bar');
    }

    function it_returns_the_whole_text_if_there_is_an_autoclosing_tag(RendererInterface $renderer)
    {
        $formerHtml = '<img src="ziggy.jpg" />';
        $newHtml = '<p>new paragraph</p><img src="ziggy.jpg" />';

        $renderer
            ->renderDiff(
                ['<img src="ziggy.jpg" />'],
                ['<p>new paragraph</p><img src="ziggy.jpg" />']
            )->shouldBeCalled()
             ->willReturn('diff');

        $this->present($formerHtml, ['data' => $newHtml])->shouldReturn('diff');
    }
}
