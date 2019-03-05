<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class AttributeExtensionSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType(\Twig_Extension::class);
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();
        $functions->shouldHaveCount(2);

        $functions[0]->getName()->shouldBeEqualTo('get_attribute_label_from_code');
        $functions[0]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
        $functions[1]->getName()->shouldBeEqualTo('is_attribute_localizable');
        $functions[1]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
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
