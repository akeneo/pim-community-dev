<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IsGrantedExtensionSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $attributeRepository, $localeRepository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_attribute_granted_extension');
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions[0]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
        $functions[1]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
        $functions[0]->getName()->shouldReturn('is_attribute_granted');
        $functions[1]->getName()->shouldReturn('is_locale_granted');
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

    function it_throws_an_exception_if_attribute_is_not_found(
        $authorizationChecker,
        $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn(null);
        $authorizationChecker->isGranted(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \LogicException('Attribute "attribute_code" not found'))
            ->during('isAttributeGranted', ['EDIT_ATTRIBUTE', 'attribute_code']);
    }

    function it_checks_if_locale_is_granted_in_terms_of_a_role(
        $authorizationChecker,
        $localeRepository,
        LocaleInterface $locale
    ) {
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted('VIEW_RESOURCE', $locale)->willReturn(true);

        $this->isLocaleGranted('VIEW_RESOURCE', 'en_US')->shouldReturn(true);
    }

    function it_throws_an_exception_if_locale_is_not_found(
        $authorizationChecker,
        $localeRepository
    ) {
        $localeRepository->findOneByIdentifier('en_US')->willReturn(null);
        $authorizationChecker->isGranted(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \LogicException('Locale "en_US" not found'))
            ->during('isLocaleGranted', ['VIEW_RESOURCE', 'en_US']);
    }
}
