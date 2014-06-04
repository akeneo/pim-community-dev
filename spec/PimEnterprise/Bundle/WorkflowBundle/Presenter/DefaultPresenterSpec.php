<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class DefaultPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Presenter\DefaultPresenter');
    }

    function it_supports_all_the_product_values(Model\AbstractProductValue $value)
    {
        $this->supports($value, [])->shouldBe(true);
    }

    function it_presents_change_using_the_injected_renderer(
        RendererInterface $renderer,
        Model\AbstractProductValue $value
    ) {
        $value->getData()->willReturn('bar');
        $renderer->renderDiff('bar', 'foo')->willReturn('diff between two simple values');

        $this->setRenderer($renderer);
        $this->present($value, ['id' => 123, 'varchar' => 'foo'])->shouldReturn('diff between two simple values');
    }
}
