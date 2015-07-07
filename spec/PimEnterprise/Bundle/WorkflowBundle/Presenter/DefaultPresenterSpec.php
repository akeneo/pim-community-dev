<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class DefaultPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Presenter\DefaultPresenter');
    }

    function it_supports_all_the_product_values(ProductValueInterface $value)
    {
        $this->supports($value, [])->shouldBe(true);
    }

    function it_presents_change_using_the_injected_renderer(RendererInterface $renderer, ProductValueInterface $value)
    {
        $value->getData()->willReturn('bar');
        $renderer->renderDiff('bar', 'foo')->willReturn('diff between two simple values');

        $this->setRenderer($renderer);
        $this->present($value, ['id' => 123, 'varchar' => 'foo'])->shouldReturn('diff between two simple values');
    }
}
