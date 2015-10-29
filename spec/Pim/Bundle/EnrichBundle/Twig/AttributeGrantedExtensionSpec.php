<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttributeGrantedExtensionSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $attributeRepository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_attribute_granted_extension');
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);
        $functions[0]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
    }

    function it_checks_if_attribute_is_granted_in_terms_of_a_role(
        $authorizationChecker,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->getGroup()->willReturn($group);
        $authorizationChecker->isGranted('EDIT_ATTRIBUTES', $group)->willReturn(true);

        $this->isAttributeGranted('EDIT_ATTRIBUTES', 'attribute_code')->shouldReturn(true);
    }

    function it_is_not_granted_if_attribute_is_not_found(
        $authorizationChecker,
        $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn(null);
        $authorizationChecker->isGranted(Argument::any())->shouldNotBeCalled();

        $this->isAttributeGranted('EDIT_ATTRIBUTE', 'attribute_code')->shouldReturn(false);
    }
}
