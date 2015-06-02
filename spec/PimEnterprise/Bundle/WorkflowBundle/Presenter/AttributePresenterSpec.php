<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Twig\LocaleExtension;
use Prophecy\Argument;

class AttributePresenterSpec extends ObjectBehavior
{
    function let(
        \Twig_Environment $twig,
        LocaleExtension $extension
    ) {
        $twig->getExtension('pim_locale_extension')->willReturn($extension);
        $extension->flag($twig, Argument::type('string'), false)->will(function ($args) {
            return sprintf('[%s]', $args[1]);
        });

        $this->setTwig($twig);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_product_attribute(AttributeInterface $attribute)
    {
        $this->supports($attribute, [])->shouldBe(true);
    }

    function it_presents_unlocalizable_and_unscopable_attribute(AttributeInterface $attribute)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getLabel()->willReturn('Name');

        $this->present($attribute, [])->shouldReturn('Name');
    }

    function it_presents_localizable_but_unscopable_attribute(AttributeInterface $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getLabel()->willReturn('Name');

        $this->present($attribute, ['locale' => 'en_US'])->shouldReturn('[en_US] - Name');
    }

    function it_presents_unlocalizable_but_scopable_attribute(AttributeInterface $attribute)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->getLabel()->willReturn('Name');

        $this->present($attribute, ['scope' => 'ecommerce'])->shouldReturn('ecommerce - Name');
    }

    function it_presents_localizable_and_scopable_attribute(AttributeInterface $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getLabel()->willReturn('Name');

        $this
            ->present($attribute, [
                'scope'  => 'ecommerce',
                'locale' => 'fr_FR',
            ])
            ->shouldReturn('[fr_FR] - ecommerce - Name');
    }
}
