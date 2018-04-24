<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class AttributeExtensionSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository, [], []);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('Twig_Extension');
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions[0]->getName()->shouldBeEqualTo('is_attribute_localizable');
        $functions[0]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
    }

    function it_returns_true_when_attribute_is_localizable($repository, AttributeInterface $attribute)
    {
        $repository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->isLocalizable()->willReturn(true);

        $this->isAttributeLocalizable('name')->shouldReturn(true);
    }

    function it_returns_false_when_attribute_is_not_localizable($repository, AttributeInterface $attribute)
    {
        $repository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->isLocalizable()->willReturn(false);

        $this->isAttributeLocalizable('name')->shouldReturn(false);
    }

    function it_throws_an_exception_if_the_attribute_is_unknown($repository)
    {
        $repository->findOneByIdentifier('name')->willReturn(null);

        $this->shouldThrow(new \LogicException('Unable to find attribute "name"'))
            ->during('isAttributeLocalizable', ['name']);
    }
}
