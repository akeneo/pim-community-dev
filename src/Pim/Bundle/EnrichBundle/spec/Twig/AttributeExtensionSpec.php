<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class AttributeExtensionSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_provides_an_get_attribute_label_from_code_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);
        $functions[0]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
        $functions[0]->getName()->shouldBeEqualTo('get_attribute_label_from_code');
    }
}
