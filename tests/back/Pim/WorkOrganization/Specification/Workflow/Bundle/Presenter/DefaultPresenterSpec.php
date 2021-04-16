<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DefaultPresenter;
use PhpSpec\ObjectBehavior;

class DefaultPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldHaveType(DefaultPresenter::class);
    }

    function it_supports_all_the_product_values()
    {
        $this->supports('foo')->shouldBe(true);
    }

    function it_presents_change_using_the_injected_renderer()
    {
        $this->present('bar', ['id' => 123, 'data' => 'foo'])->shouldReturn([
            'before' => 'bar',
            'after' => 'foo']
        );
    }
}
