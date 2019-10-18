<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DefaultPresenter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class DefaultPresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_presenter()
    {
        $this->shouldHaveType(DefaultPresenter::class);
    }

    function it_supports_all_the_product_values()
    {
        $this->supports('foo')->shouldBe(true);
    }

    function it_presents_change_using_the_injected_renderer(RendererInterface $renderer)
    {
        $renderer->renderDiff('bar', 'foo')->willReturn('diff between two simple values');
        $this->setRenderer($renderer);

        $this->present('bar', ['id' => 123, 'data' => 'foo'])->shouldReturn('diff between two simple values');
    }
}
