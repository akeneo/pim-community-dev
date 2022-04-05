<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Twig;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
        $this->shouldHaveType(AbstractExtension::class);
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions[0]->shouldBeAnInstanceOf(TwigFunction::class);
        $functions[1]->shouldBeAnInstanceOf(TwigFunction::class);
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
