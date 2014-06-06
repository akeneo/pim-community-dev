<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

class AttributePresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_product_attribute(Model\AbstractAttribute $attribute)
    {
        $this->supports($attribute, [])->shouldBe(true);
    }

    function it_presents_unlocalizable_and_unscopable_attribute(Model\AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->__toString()->willReturn('Name');

        $this->present($attribute, [])->shouldReturn('Name');
    }

    function it_presents_localizable_but_unscopable_attribute(Model\AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->__toString()->willReturn('Name');

        $this->present($attribute, [])->shouldReturn('Name <i class="icon-globe"></i>');
    }

    function it_presents_unlocalizable_but_scopable_attribute(Model\AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->__toString()->willReturn('Name');

        $this->present($attribute, ['__context__' => ['scope' => 'ecommerce']])->shouldReturn('ecommerce - Name');
    }

    function it_presents_localizable_and_scopable_attribute(Model\AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->__toString()->willReturn('Name');

        $this->present($attribute, ['__context__' => ['scope' => 'ecommerce']])->shouldReturn('ecommerce - Name <i class="icon-globe"></i>');
    }
}
