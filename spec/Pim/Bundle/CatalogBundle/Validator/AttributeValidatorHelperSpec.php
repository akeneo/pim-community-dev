<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

class AttributeValidatorHelperSpec extends ObjectBehavior
{
    function let(LocaleRepository $localeRepository, ChannelRepository $scopeRepository)
    {
        $this->beConstructedWith($localeRepository, $scopeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper');
    }

    function it_throws_an_exception_when_attribute_localizable_requirement_is_not_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isLocalizable()->willReturn(true);
        $description->getCode()->willReturn('description');
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');


        $this->shouldThrow(new \LogicException('Attribute "description" expects a locale, none given.'))
            ->during('validateLocale', [$description, null]);

        $this->shouldThrow(new \LogicException('Attribute "name" does not expect a locale, "en_US" given.'))
            ->during('validateLocale', [$name, 'en_US']);
    }

    function it_throws_an_exception_when_locale_is_not_activated(
        $localeRepository,
        AttributeInterface $description
    ) {
        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $description->isLocalizable()->willReturn(true);
        $description->getCode()->willReturn('description');

        $this->shouldThrow(
            new \LogicException('Attribute "description" expects an existing and activated locale, "en_US" given.')
        )->during('validateLocale', [$description, 'en_US']);
    }

    function it_throws_an_exception_when_attribute_scopable_requirement_is_not_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isScopable()->willReturn(true);
        $description->getCode()->willReturn('description');
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');


        $this->shouldThrow(new \LogicException('Attribute "description" expects a scope, none given.'))
            ->during('validateScope', [$description, null]);

        $this->shouldThrow(new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.'))
            ->during('validateScope', [$name, 'ecommerce']);
    }

    function it_throws_an_exception_when_scope_is_not_activated(
        $scopeRepository,
        AttributeInterface $description
    ) {
        $scopeRepository->getChannelCodes()->willReturn([]);
        $description->isScopable()->willReturn(true);
        $description->getCode()->willReturn('description');

        $this->shouldThrow(
            new \LogicException('Attribute "description" expects an existing scope, "ecommerce" given.')
        )->during('validateScope', [$description, 'ecommerce']);
    }
}
