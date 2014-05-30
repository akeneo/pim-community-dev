<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class DefaultPresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_presenter()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Presenter\DefaultPresenter');
    }

    function it_supports_all_the_product_values(Model\AbstractProductValue $value)
    {
        $this->supports($value, [])->shouldBe(true);
    }

    function it_presents_change_using_the_injected_renderer(
        $renderer,
        $factory,
        \Diff $diff,
        Model\AbstractProductValue $value
    ) {
        $value->getData()->willReturn('bar');

        $factory->create('bar', 'foo')->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between two simple values');

        $this->present($value, ['id' => 123, 'varchar' => 'foo'])->shouldReturn('diff between two simple values');
    }
}
