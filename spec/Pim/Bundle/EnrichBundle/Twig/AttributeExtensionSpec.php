<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $icons = [
            'file'  => 'archive',
            'image' => 'picture'
        ];
        $this->beConstructedWith($icons);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_provides_an_attribute_icon_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);
        $functions->shouldHaveKey('attribute_icon');
        $functions['attribute_icon']->shouldBeAnInstanceOf('\Twig_Function_Method');
    }

    function its_attributeIcon_method_returns_the_icon_for_the_provided_attribute_type()
    {
        $this->attributeIcon('file')->shouldReturn('archive');
        $this->attributeIcon('image')->shouldReturn('picture');
    }

    function its_attributeIcon_method_returns_an_empty_string_if_icon_is_not_found()
    {
        $this->attributeIcon('metric')->shouldReturn('');
        $this->attributeIcon(null)->shouldReturn('');
    }
}
